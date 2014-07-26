metaphore
=========

PHP cache slam defense using (memcached) semaphore to prevent dogpile effect (aka clobbering updates, stampending herd or Slashdot effect).

Problem
-------

Too many requests hit your website at the same time to regenerate same content slamming your database.

Solution
--------

First request generates new content while all the subsequent requests get (stale) content from cache until new one is re-generated.

Similar solutions:

* [Varnish - Grace](https://www.varnish-cache.org/trac/wiki/VCLExampleGrace)
* [LSDCache](https://github.com/gsmlabs/LSDCache) - this lib is lightweight version of lsdcache (imho lsdcache grew too big into multi-purpose cache library while metaphore strives to be simple to do just one thing and to do it well)

Usage
-----

In composer.json file:

```
"require": {
	"sobstel/metaphore": "dev-master"
}
```

In your PHP file:

``` php
use Metaphore\Cache;

// initialize $memcached object (new Memcached())

$cache = new Cache($memcached);
$cache->cache($key, function(){
    // generate content
}, $ttl);
```
