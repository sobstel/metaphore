metaphore
=========

PHP cache slam defense using (memcached) semaphore to prevent dogpile effect / clobbering updates / stampending requests / stampending herd.

Problem
-------

Too many requests hit your website at the same time to regenerate same content slamming your database.

More reading:

* https://code.google.com/p/memcached/wiki/NewProgrammingTricks#Avoiding_stampeding_herd
* http://www.php.net/manual/en/mysqlnd-qc.slam-defense.php
* https://www.varnish-cache.org/trac/wiki/VCLExampleGrace

Solution
--------

First request generates new content while all the subsequent requests get (stale) content from cache until new one is re-generated.

Similar solutions:

* [Varnish - Grace](https://www.varnish-cache.org/trac/wiki/VCLExampleGrace)
* [LSDCache](https://github.com/gsmlabs/LSDCache) - metaphore is lightweight version of lsdcache (inmho lsdcache grew too big and metaphore focuses on just one thing to do it well)

Usage
-----

``` php
use Metaphore\Cache;

$cache = new Cache($memcached);
$cache->cache($key, function(){
    // generate content
}, $ttl);
```
