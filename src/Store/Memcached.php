<?php
namespace Metaphore\Store;

use Metaphore\Store\StoreInterface;

class Memcached implements StoreInterface
{
    // over some threshold, it's treated as timestamp (and not ttl)
    // http://www.php.net/manual/en/memcached.expiration.php
    const MAX_TTL = 2592000;

    // key size is limited
    // https://code.google.com/p/memcached/wiki/NewProgrammingTricks#Reducing_key_size
    const MAX_KEY_LENGTH = 250;

    /*** @var $memcached */
    protected $memcached;

    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    public function set($key, $value, $ttl)
    {
        return $this->memcached->set($this->prepareTtl($key), $value, $this->prepareTtl($ttl));
    }

    public function get($key)
    {
        return $this->memcached->get($this->prepareKey($key));
    }

    public function add($key, $value, $ttl)
    {
        return $this->memcached->add($this->prepareTtl($key), $value, $this->prepareTtl($ttl));
    }

    public function delete($key)
    {
        return $this->memcached->delete($this->prepareKey($key));
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
            return (time() + $ttl); // actual timestamp must be passed if higher than MAX_TTL
        }

        return $ttl;
    }
}
