<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Predis\Client;

class PredisStore implements ValueStoreInterface, LockStoreInterface
{
    protected $predis;

    public function __construct(Client $predisInstance)
    {
        $this->predis = $predisInstance;
    }

    public function set($key, $value, $ttl)
    {
        return $this->predis->set($key, serialize($value), "EX", $ttl);
    }

    public function get($key)
    {
        $value = $this->predis->get($key);

        if ($value) {
            $value = unserialize($value);
        }

        return $value === null ? false : $value;
    }

    public function delete($key)
    {
        return $this->predis->del($key);
    }

    public function add($key, $value, $ttl)
    {
        return $this->predis->set($key, serialize($value), "EX", $ttl, "NX");
    }
}
