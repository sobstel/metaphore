CHANGELOG
=========

2.0.1
-----

* Fixed: PHP 8.2+ requirement in composer.json file.

2.0.0
-----

* Changed: PHP 8.2+ required now (dropped support for 5.x/7.x though it might still work)
* Changed: bumped predis dependency to 2.0.3+
* Fixed: unit tests for PHP 8.2+

1.2.6
-----

* Added: allow to configure serializer for redis store (wojciechlukoszek)

1.2.5
-----

* Fixed: file store deadlock issue (lchenay)

1.2.4
-----

* Added: file store

1.2.3
-----

* Fixed: redis: detecting non-existing value (null) (romannowicki)

1.2.2
-----

* Fixed: redis store does not handle compound values

1.2.1
-----

* Added: PredisStore (redis store) (wojciechlukoszek)
* Changed: real-life memcache/memcached stores tests
* Changed: Cache::setResult() accepts $ttl as a integer too

1.2.0
-----

* Changed: pre-defined default lock ttl (5s) instead of fancy (and logicless)
  algorithm based on grace ttl

1.1.0
-----

* NoStaleCache improvements
  * Removed: dependency on symfony event-dispatcher
  * Added: possible to return value using noStaleCacheEvent
  * Added: possible to pass callback to cache() call as last argument

1.0.1
-----

* Added: MemcacheStore (krzysztof-magosa)
