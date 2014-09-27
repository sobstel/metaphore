<?php
namespace Metaphore\Store;

interface LockStoreInterface
{
    /**
     * @return bool
     */
    public function add($key, $value, $ttl);

    /**
     * @return bool
     */
    public function delete($key);
}
