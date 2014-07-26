metaphore
=========

PHP cache slam defense using (memcached) semaphore to prevent dogpile effect (aka clobbering updates, stampending herd or Slashdot effect).

Problem: too many requests hit your website at the same time to regenerate same content slamming your database. It might happen after the cache was expired.

Solution: first request generates new content while all the subsequent requests get (stale) content from cache until it's refreshed by the first request.

Read (http://www.sobstel.org/blog/preventing-dogpile-effect/)[http://www.sobstel.org/blog/preventing-dogpile-effect/] for more details.

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
