<?php
namespace Metaphore\Store;

use Metaphore\Store\LockStoreInterface;

interface ValueStoreInterface
{
    public function set($key, $value, $ttl);

    public function get($key);

    /**
     * @return bool
     */
    public function add($key, $value, $ttl);

    public function delete($key);
}
