<?php
namespace Metaphore\Store;

class Mock implements StoreInterface
{
    protected $values = [];

    public function set($key, $value, $ttl)
    {
        $this->values[$key] = $value;
        return true;
    }

    public function get($key)
    {
        if (!isset($this->values[$key])) {
            return false;
        }

        return $this->values[$key];
    }

    public function add($key, $value, $ttl)
    {
        if (isset($this->values[$key])) {
            return false;
        }

        $this->values[$key] = $value;
        return true;
    }

    public function delete($key)
    {
        if (!isset($this->values[$key])) {
            return false;
        }

        unset($this->values[$key]);
        return true;
    }
}
