<?php
namespace Metaphore\Store;

interface LockStoreInterface
{
    public function set($key, $value, $ttl);

    public function delete($key);
}
