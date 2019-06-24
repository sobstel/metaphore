<?php
namespace Metaphore\Store;

use Metaphore\Serializer\NativeSerializer;
use Metaphore\Serializer\SerializerInterface;
use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Predis\Client;

class PredisStore implements ValueStoreInterface, LockStoreInterface
{
    protected $predis;
    protected $serializer;

    public function __construct(Client $predis, SerializerInterface $serializer = null)
    {
        $this->predis = $predis;
        $this->serializer = $serializer ?: new NativeSerializer();
    }

    public function set($key, $value, $ttl)
    {
        return $this->predis->set($key, $this->serializer->serialize($value), "EX", $ttl);
    }

    public function get($key)
    {
        $value = $this->predis->get($key);

        if ($value) {
            $value = $this->serializer->unserialize($value);
        }

        return ($value === null ? false : $value);
    }

    public function delete($key)
    {
        return $this->predis->del($key);
    }

    public function add($key, $value, $ttl)
    {
        return $this->predis->set($key, $this->serializer->serialize($value), "EX", $ttl, "NX");
    }
}
