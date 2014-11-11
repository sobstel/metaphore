<?php
namespace Metaphore;

use Metaphore\Cache;
use Metaphore\Ttl;

class NoStaleCacheEvent
{
    protected $cache;

    protected $key;

    protected $cachedCallable;

    protected $ttl;

    protected $result = null;

    public function __construct(Cache $cache, $key, callable $cachedCallable, Ttl $ttl)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->cachedCallable = $cachedCallable;
        $this->ttl = $ttl;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return callable
     */
    public function getCachedCallable()
    {
        return $this->cachedCallable;
    }

    /**
     * @return Ttl
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function hasResult()
    {
        return ($this->result !== null);
    }

    public function getResult()
    {
        return $this->result;
    }
}
