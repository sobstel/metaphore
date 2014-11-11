<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Metaphore\Store\AbstractMemcachedStore;
use Memcached;

class MemcachedStore extends AbstractMemcachedStore implements ValueStoreInterface, LockStoreInterface
{
    /*** @var Memcached */
    protected $memcached;

    /**
     * @param Memcached
     */
    public function __construct(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    public function set($key, $value, $ttl)
    {
        return $this->memcached->set($this->prepareKey($key), $value, $this->prepareTtl($ttl));
    }

    public function get($key)
    {
        return $this->memcached->get($this->prepareKey($key));
    }

    public function add($key, $value, $ttl)
    {
        return $this->memcached->add($this->prepareKey($key), $value, $this->prepareTtl($ttl));
    }

    public function delete($key)
    {
        return $this->memcached->delete($this->prepareKey($key));
    }
}
