<?php
namespace Metaphore;

use Metaphore\Store\StoreInterface;
use Metaphore\Value;

class Cache
{
    /*** @var \Metaphore\Store\StoreInterface */
    protected $store;

    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    public function __destruct()
    {
        // TODO: clear unlocked locks
    }

    /**
     * @param string
     * @param callable
     * @param int
     * @param int Grace period (how long to serve stale content)
     */
    public function cache($key, callable $callable, $ttl, $grace_ttl, $lock_ttl = null)
    {
        $value = $this->store->get($key);

        if ($this->isValue($value) && $value->isFresh()) {
            return $value->getResult();
        }

        $lock_key = sprintf('%.lock', $key);

        if (!$lock_ttl || $lock_ttl > $grace_ttl) {
            // educated guess (remove lock early enough so if anything goes wrong
            // with first process, another one can pick up)
            $lock_ttl = (int)($grace_ttl / 2);
        }

        // lock
        $locked = $this->store->add($lock_key, 1, $lock_ttl);

        if (!$locked) {
            if ($this->isValue($value)) { // stale
                return $value->getResult();
            }

            // TODO: deadlock handling
        }

        // TODO: remember lock

        $result = call_user_func($callable);

        $expiration_timestamp = time() + $ttl;
        $value = new Value($result, $expiration_timestamp);

        $real_ttl = $ttl + $grace_ttl; // in fact cache for longer, so stale result might be served if needed
        $this->store->set($key, $value, $real_ttl);

        // unlock
        $this->store->delete($lock_key);

        return $result;
    }

    public function delete($key)
    {
        $this->store->delete($key);
    }

    protected function isValue($value) {
        return ($value !== false && $value instanceof \Metaphore\Value);
    }
}
