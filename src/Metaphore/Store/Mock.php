<?php
namespace Metaphore\Store;

use Metaphore\Store\StoreInterface;
use Metaphore\Value;

class Mock implements StoreInterface
{
    protected $values = [];
    protected $locks = [];

    public function set($key, Value $value, $ttl)
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

    public function add($key, Value $value, $ttl)
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
