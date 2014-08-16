<?php
namespace Metaphore;

use Metaphore\Value;
use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\Memcached as MemcachedStore;

class Cache
{
    /*** @var \Metaphore\Store\ValueStoreInterface */
    protected $valueStore;

    /*** @var \Metaphore\LockManager */
    protected $lockManager;

    /*** @var int How long to serve stale content while new one is being generated */
    protected $grace_ttl = 60;

    /**
     * @param \Metaphore\Store\ValueStoreInterface
     * @param \Metaphore\Store\LockStoreInterface
     */
    public function __construct(ValueStoreInterface $valueStore, LockManager $lockManager = null)
    {
        $this->valueStore = $valueStore;

        if (!$lockManager) {
            $lockManager = new LockManager($valueStore);
        }
        $this->lockManager = $lockManager;
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
        $value = $this->valueStore->get($key);

        if ($this->isValue($value) && !$value->isStale()) {
            return $value->getResult();
        }

        if (!$grace_ttl) {
            $grace_ttl = $this->grace_ttl;
        }

        $lock_acquired = $this->lockManager->acquire($key, $grace_ttl);

        if (!$lock_acquired && $this->isValue($value)) {
            // serve stale if present
            return $value->getResult();
        }

        $result = call_user_func($callable);

        $expiration_timestamp = time() + $ttl;
        $value = new Value($result, $expiration_timestamp);

        $real_ttl = $ttl + $grace_ttl; // $grace_ttl added, so stale result might be served if needed
        $this->valueStore->set($key, $value, $real_ttl);

        if ($lock_acquired) {
            $this->lockManager->release($key);
        }

        return $result;
    }

    public function get($key)
    {
        $value = $this->valueStore->get($key);

        if ($this->isValue($value)) {
            return $value->getResult();
        }

        return false;
    }

    public function delete($key)
    {
        $this->valueStore->delete($key);
    }

    public function getValueStore()
    {
        return $this->valueStore;
    }

    public function getLockManager()
    {
        return $this->lockManager;
    }

    protected function isValue($value)
    {
        return ($value !== false && $value instanceof \Metaphore\Value);
    }
}
