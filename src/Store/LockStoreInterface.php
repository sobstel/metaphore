<?php
namespace Metaphore\Store;

interface LockStoreInterface
{
    /**
     * Add new value only if it's not already stored.
     *
     * This operation should be atomic.
     *
     * @return bool
     */
    public function add($key, $value, $ttl);

    /**
     * @return bool
     */
    public function delete($key);
}
