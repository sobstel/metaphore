<?php
namespace Metaphore\Store;

abstract class AbstractMemcachedStore
{
    // when over 30 days, it's treated as unix timestamp (number of seconds
    // since January 1, 1970, as an integer), and not a number of seconds
    // starting from current time
    // http://php.net/manual/en/memcache.set.php
    const MAX_TTL = 2592000;

    // key size is limited
    // https://code.google.com/p/memcached/wiki/NewProgrammingTricks#Reducing_key_size
    const MAX_KEY_LENGTH = 250;

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
