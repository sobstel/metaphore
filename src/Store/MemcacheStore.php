<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Metaphore\Store\AbstractMemcachedStore;
use Memcache;

class MemcacheStore extends AbstractMemcachedStore implements ValueStoreInterface, LockStoreInterface
{
    /*** @var Memcache */
    protected $memcache;

    /**
     * @param Memcache
     */
    public function __construct(Memcache $memcache)
    {
        $this->memcache = $memcache;
    }

    public function set($key, $value, $ttl)
    {
        return $this->memcache->set($this->prepareKey($key), $value, 0, $this->prepareTtl($ttl));
    }

    public function get($key)
    {
        return $this->memcache->get($this->prepareKey($key));
    }

    public function add($key, $value, $ttl)
    {
        return $this->memcache->add($this->prepareKey($key), $value, 0, $this->prepareTtl($ttl));
    }

    public function delete($key)
    {
        return $this->memcache->delete($this->prepareKey($key));
    }
}
