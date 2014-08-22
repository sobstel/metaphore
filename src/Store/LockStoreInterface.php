<?php
namespace Metaphore\Store;

interface LockStoreInterface
{
    public function add($key, $value, $ttl);

    public function delete($key);
}
