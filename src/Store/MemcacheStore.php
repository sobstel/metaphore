<?php
namespace Metaphore\Store;

use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Memcache;

class MemcacheStore implements ValueStoreInterface, LockStoreInterface
{
    // when over 30 days, it's treated as unix timestamp (number of seconds
    // since January 1, 1970, as an integer), and not a number of seconds
    // starting from current time
    // http://php.net/manual/en/memcache.set.php
    const MAX_TTL = 2592000;

    // key size is limited
    // https://code.google.com/p/memcached/wiki/NewProgrammingTricks#Reducing_key_size
    const MAX_KEY_LENGTH = 250;

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

    protected function prepareKey($key)
    {
        if (strlen($key) > self::MAX_KEY_LENGTH) {
            $key = substr($key, 0, (self::MAX_KEY_LENGTH - 43)).'___'.sha1($key);
        }

        return $key;
    }

    protected function prepareTtl($ttl)
    {
        if ($ttl > self::MAX_TTL) {
            return (time() + $ttl); // timestamp must be passed if higher than MAX_TTL
        }

        return $ttl;
    }
}
