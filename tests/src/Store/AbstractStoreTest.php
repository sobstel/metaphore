<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\MemcachedStore;

abstract class AbstractStoreTest extends \PHPUnit_Framework_TestCase
{
    protected $store;

    /**
     * @dataProvider keyValuePairsProvider
     */
    public function testSetAndGet($key, $value, $ttl)
    {
        $this->store->set($key, $value, $ttl);
        $this->assertEquals($value, $this->store->get($key));
    }

    /**
     * @dataProvider keyValuePairsProvider
     */
    public function testAddAndGet($key, $value, $ttl)
    {
        $this->store->add($key, $value, $ttl);
        $this->assertEquals($value, $this->store->get($key));
    }

    /**
     * @dataProvider keyValuePairsProvider
     */
    public function testSetAndAddAndDelete($key, $value, $ttl)
    {
        $this->store->set($key, $value, $ttl);
        $this->store->add($key, 'dummy (should be not set)', $ttl);

        $this->assertEquals($value, $this->store->get($key));
    }

    /**
     * @dataProvider keyValuePairsProvider
     */
    public function testSetAndDelete($key, $value, $ttl)
    {
        $this->store->set($key, $value, $ttl);
        $this->store->delete($key);

        $this->assertEmpty($this->store->get($key));
    }

    public function keyValuePairsProvider()
    {
        return [
            ['messi', 'messiah10', 30],
            [str_repeat(sha1(mt_rand()), 10), 'messy argentino', 30], // long key (400 chars)
            ['key', 'value', MemcachedStore::MAX_TTL + 3600], // big ttl
            ['jugador1', [2 => 'di', 3 => 'maria'], 30], // compound value (array)
            ['jugador2', new \StdClass(), 30], // compound value (object)
        ];
    }
}
