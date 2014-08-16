<?php
namespace Metaphore;

use Metaphore\Value;
use Metaphore\Store\StoreInterface;
use Metaphore\Store\Memcached as MemcachedStore;

class Cache
{
    /*** @var \Metaphore\Store\StoreInterface */
    protected $store;

    /*** @var int How long to serve stale content while new one is being generated */
    protected $grace_ttl = 60;

    /**
     * @param \Memcached|\Metaphore\Store\StoreInterface
     */
    public function __construct($store)
    {
        if ($store instanceof \Memcached) { // you can pass \Memcached directly and it will be wrapped by Store\Memcached for you
            $store = new MemcachedStore($store);
        }

        if (!($store instanceof StoreInterface)) {
            throw new \Exception('Store must implement Metaphore\Store\StoreInterface');
        }

        $this->store = $store;
    }

    public function setGraceTtl($grace_ttl)
    {
        $this->grace_ttl = $grace_ttl;
    }

    /**
     * @param string
     * @param callable
     * @param int
     * @param int
     */
    public function cache($key, callable $callable, $ttl, $grace_ttl = null)
    {
        $value = $this->store->get($key);

        if ($this->isValue($value) && !$value->isStale()) {
            return $value->getResult();
        }

        if (!$grace_ttl) {
            $grace_ttl = $this->grace_ttl;
        }

        $lock_acquired = $this->acquireLock($key, $grace_ttl);

        if (!$lock_acquired && $this->isValue($value)) {
            // serve stale if present
            return $value->getResult();
        }

        $result = call_user_func($callable);

        $expiration_timestamp = time() + $ttl;
        $value = new Value($result, $expiration_timestamp);

        $real_ttl = $ttl + $grace_ttl; // $grace_ttl added, so stale result might be served if needed
        $this->store->set($key, $value, $real_ttl);

        if ($lock_acquired) {
            $this->releaseLock($key);
        }

        return $result;
    }

    public function get($key)
    {
        $value = $this->store->get($key);

        if ($this->isValue($value)) {
            return $value->getResult();
        }

        return $value;
    }

    public function delete($key)
    {
        $this->store->delete($key);
    }

    protected function isValue($value)
    {
        return ($value !== false && $value instanceof \Metaphore\Value);
    }

    protected function prepareLockKey($key)
    {
        return sprintf('%s.lock', $key);
    }

    protected function acquireLock($key, $grace_ttl)
    {
        // educated guess (remove lock early enough so if anything goes wrong
        // with first process, another one can pick up)
        // SMELL: a bit problematic, why $grace_ttl/2 ???
        $lock_ttl = max(1, (int)($grace_ttl/2));

        return $this->store->add($this->prepareLockKey($key), 1, $lock_ttl);
    }

    protected function releaseLock($key)
    {
        $this->store->delete($this->prepareLockKey($key));
    }
}
