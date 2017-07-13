metaphore
=========

PHP cache slam defense using a semaphore to prevent dogpile effect (aka clobbering updates, stampending herd or
Slashdot effect).

**Problem**: too many requests hit your website at the same time while it tries to regenerate same content slamming 
your database, eg. when cache expired.

**Solution:** first request generates new content while all the subsequent requests get (stale) content from cache
until it's refreshed by the first request.

Read [http://www.sobstel.org/blog/preventing-dogpile-effect/](http://www.sobstel.org/blog/preventing-dogpile-effect/)
for more details.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sobstel/metaphore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sobstel/metaphore/?branch=master)
[![Build Status](https://travis-ci.org/sobstel/metaphore.svg?branch=master)](https://travis-ci.org/sobstel/metaphore)

Usage
-----

In composer.json file:

```
"require": {
  "sobstel/metaphore": "1.2.*"
}
```

(or just `composer require sobstel/sesshin`)

In your PHP file:

``` php
use Metaphore\Cache;
use Metaphore\Store\MemcachedStore;

// initialize $memcached object (new Memcached())

$cache = new Cache(new MemcachedStore($memcached));
$cache->cache('key', function() {
    // generate content
}, 30);
```

Public API (methods)
--------------------

- `__construct(ValueStoreInterface $valueStore, LockManager $lockManager = null)`

- `cache($key, callable $callable, [$ttl, [$onNoStaleCacheCallable]])` - returns result
- `delete($key)`
- `getValue($key)` - returns Value object
- `setResult($key, $result, Ttl $ttl)` - sets result (without anti-dogpile-effect mechanism)
- `onNoStaleCache($callable)`

- `getValueStore()`
- `getLockManager()`

Value store vs lock store
-------------------------

Cache values and locks can be handled by different stores. 

``` php
$valueStore = new Metaphore\MemcachedStore($memcached);

$lockStore = new Your\Custom\MySQLLockStore($connection);
$lockManager = new Metaphore\LockManager($lockStore);

$cache = new Metaphore\Cache($valueStore, $lockManager);
```

By default - if no 2nd argument passed to Cache constructor - value store is used as a lock store.

Sample use case might be to have custom MySQL GET_LOCK/RELEASE_LOCK for locks and still use in-built
Memcached store for storing values.

Time-to-live
------------

You can pass simple integer value...

``` php
$cache->cache('key', callback, 30); // cache for 30 secs
```

.. or use more advanced `Metaphore\TTl` object, which gives you control over grace period and lock ttl.

``` php
// $ttl, $grace_ttl, $lock_ttl
$ttl = new Ttl(30, 60, 15);

$cache->cache('key', callback, $ttl);
```

- `$ttl` - regular cache time (in seconds)
- `$grace_ttl` - grace period, how long to allow to serve stale content while new one is being generated (in seconds),
   similar to HTTP's stale-while-revalidate, default is 60s
- `$lock_ttl` - lock time, how long to prevent other request(s) from generating same content, default is 5s

Ttl value is added to current timestamp (`time() + $ttl`). 

No stale cache
--------------

In rare situations, when cache gets expired and there's no stale (generated earlier) content available, all requests
will start generating new content.

You can add listener to catch this:

``` php
$cache->onNoStaleCache(function (NoStaleCacheEvent $event) {
    Logger::log(sprintf('no stale cache detected for key %s', $event->getKey()));
});
```

You can also affect value that is returned:

``` php
$cache->onNoStaleCache(function (NoStaleCacheEvent $event) {
    $event->setResult('new custom result');
});
```

Tests
-----

Run all tests: `phpunit`.

If no memcached or/and redis installed: `phpunit --exclude-group=notisolated` or `phpunit --exclude-group=memcached,redis`.
