<?php
namespace Metaphore\Store;

use Metaphore\Value;

interface StoreInterface
{
    public function set($key, Value $value, $ttl);

    public function get($key);

    public function add($key, Value $value, $ttl);

    public function delete($key);
}
