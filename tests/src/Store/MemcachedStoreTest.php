<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\MemcachedStore;
use Memcached;

/**
 * @group notisolated
 * @group memcached
 * @group php-memcached
 */
class MemcachedStoreTest extends AbstractStoreTestCase
{
    public function setUp(): void
    {
        $client = new Memcached;
        $client->addServer('127.0.0.1', 11211);

        $this->store = new MemcachedStore($client);
    }

    public function tearDown(): void
    {
        $this->store = null;
    }

    /**
     * @dataProvider keyValuePairsProvider
     */
    public function testTimestampPassedIfTtlBiggerThan30Days($key, $value, $ttl)
    {
        $method = (new \ReflectionClass($this->store))->getMethod('prepareTtl');
        $method->setAccessible(true);

        $prepared_ttl = $method->invokeArgs($this->store, [$ttl]);

        // value can be either lower than 30 days or set to unix timestamp in the future
        $this->assertTrue($prepared_ttl <= MemcachedStore::MAX_TTL || $prepared_ttl >= time());
    }
}
