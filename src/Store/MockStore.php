<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;

class MockStore implements ValueStoreInterface, LockStoreInterface
{
    protected $values = [];

    public function set($key, $value, $ttl)
    {
        $this->values[$key] = $value;
        return true;
    }

    public function get($key)
    {
        if (!$this->exists($key)) {
            return false;
        }

        return $this->values[$key];
    }

    public function add($key, $value, $ttl)
    {
        if ($this->exists($key)) {
            return false;
        }

        $this->values[$key] = $value;
        return true;
    }

    public function delete($key)
    {
        if (!$this->exists($key)) {
            return false;
        }

        unset($this->values[$key]);
        return true;
    }

    protected function exists($key)
    {
        return isset($this->values[$key]);
    }
}
