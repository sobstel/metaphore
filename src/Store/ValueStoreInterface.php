<?php
namespace Metaphore\Store;

use Metaphore\Store\LockStoreInterface;

interface ValueStoreInterface
{
    /**
     * @return bool
     */
    public function set($key, $value, $ttl);

    /**
     * @return mixed
     */
    public function get($key);

    /**
     * @return bool
     */
    public function add($key, $value, $ttl);

    /**
     * @return bool
     */
    public function delete($key);
}
