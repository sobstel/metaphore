metaphore
=========

PHP cache slam defense using (memcached) semaphore to prevent dogpile effect (aka clobbering updates, stampending herd or Slashdot effect).

Problem: too many requests hit your website at the same time to regenerate same content slamming your database. It might happen after the cache was expired.

Solution: first request generates new content while all the subsequent requests get (stale) content from cache until it's refreshed by the first request.

Read [http://www.sobstel.org/blog/preventing-dogpile-effect/](http://www.sobstel.org/blog/preventing-dogpile-effect/) for more details.

Metaphore is rewrite of [LSDCache](https://github.com/gsmlabs/LSDCache), which has been successfully used in many high-traffic production web apps. I just believe that LSDCache has grown too big into multi-purpose cache library while metaphore strives to be simple to do just one thing and to do it well.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sobstel/metaphore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sobstel/metaphore/?branch=master)
[![Build Status](https://travis-ci.org/sobstel/metaphore.svg?branch=master)](https://travis-ci.org/sobstel/metaphore)
[![Build Status](https://scrutinizer-ci.com/g/sobstel/metaphore/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sobstel/metaphore/build-status/master)

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
use Metaphore\Store\MemcachedStore;

// initialize $memcached object (new Memcached())

$cache = new Cache(new MemcachedStore($memcached));
$cache->cache($key, function(){
    // generate content
}, $ttl);
```
