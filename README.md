metaphore
=========

PHP cache slam defense using a semaphore to prevent dogpile effect (aka clobbering updates, stampending herd or
Slashdot effect).

**Problem**: too many requests hit your website at the same time to regenerate same content slamming your database.
It might happen after the cache was expired.

**Solution:** first request generates new content while all the subsequent requests get (stale) content from cache
until it's refreshed by the first request.

Read [http://www.sobstel.org/blog/preventing-dogpile-effect/](http://www.sobstel.org/blog/preventing-dogpile-effect/)
for more details.

*Metaphore* is a rewrite of [LSDCache](https://github.com/gsmlabs/LSDCache), which has been successfully used in many
high-traffic production web apps. I just believe that LSDCache has grown too big into multi-purpose cache library (which
is not really maintained anymore) while *Metaphore* strives to be simple to do just one thing and to do it well.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sobstel/metaphore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sobstel/metaphore/?branch=master)
[![Build Status](https://travis-ci.org/sobstel/metaphore.svg?branch=master)](https://travis-ci.org/sobstel/metaphore)
[![Build Status](https://scrutinizer-ci.com/g/sobstel/metaphore/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sobstel/metaphore/build-status/master)

Usage
-----

In composer.json file:

```
"require": {
  "sobstel/metaphore": "1.0.*"
}
```

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

- `cache($key, callable $callable, $ttl)` - returns result
- `delete($key)`
- `getValue($key)` - returns Value object
- `setResult($key, $result, Tttl $ttl)` - sets result (without anti-dogpile-effect mechanism)

- `getValueStore()`
- `getLockManager()`
- `getEventDispatcher()`

Value store vs lock store
-------------------------

Cache values and locks can be handled by different stores. At this moment there's just MemcachedStore, but it's
possible - for example - to write and use external MySQL GET_LOCK/RELEASE_LOCK for locks and still us in-built
Memcached store for storing values.

By default, value store is used for lock store if no 2nd argument passed to Cache constructor.


``` php
$valueStore = new Metaphore\MemcachedStore($memcached);

$lockStore = new Your\Custom\MySQLLockStore($connection);
$lockManager = new Metaphore\LockManager($lockStore);

$cache = new Metaphore\Cache($valueStore, $lockManager);
```

Time-to-live
------------

You can pass simple integer value...

``` php
$cache->cache('key', callback, 30); // cache for 30 secs
```

.. or use more advanced `Metaphore\TTl` object, which allows to have control over grace period and lock ttl.

``` php
// $ttl, $grace_ttl, $lock_ttl
$ttl = new Ttl(30, 60, 15);

$cache->cache('key', callback, $ttl);
```

- `$ttl` - regular cache time (in seconds)
- `$grace_ttl` - grace period, how long to allow to serve stale content while new one is being generated (in seconds),
  default is 60s
- `$lock_ttl` - lock time, hwo long to prevent other request(s) to start generating content, default is `($grace_ttl / 2)`

NO_STALE_CACHE event
--------------------

In rare situations, when cache gets expired and there's no stale (generated earier) content available, all requests
will start generating new content.

You can add listener to catch this:

``` php
$cache->getEventDispatcher()->addListener(
    'NO_STALE_CACHE',
    function (\Symfony\Component\EventDispatcher\GenericEvent $event) {
        Logger::log(sprintf('no stale cache detected for key %s', $event['key']))
    }
);
```

Available event properties: `'key'`, `'callable'`, `'ttl'`.

Credits
-------

Thanks to Łukasz Łoboda, Wiktor Malinowski (and anyone I forgot, sorry) for review and comments.
