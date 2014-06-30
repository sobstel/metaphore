<?php
namespace Metaphore\Store;

interface StoreInterface
{
    public function set($key, $value, $ttl);

    public function get($key);

    public function add($key, $value, $ttl);

    public function delete($key);
}
