<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\MemcacheStore;

class MemcacheStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $this->store = new MemcacheStore($this->createMemcacheMock());
    }

    public function tearDown()
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
        $this->assertTrue($prepared_ttl <= MemcacheStore::MAX_TTL || $prepared_ttl >= time());
    }

    protected function createMemcacheMock()
    {
        // we need values set by set() call to be available for get() call
        $mockValue = new \StdClass;
        $mockValue->key = null;
        $mockValue->value = null;

        $memcacheMock = $this->getMockBuilder('Memcache')
            ->setMethods(['set', 'add', 'get', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        # Memcache::set
        $memcacheMock
            ->method('set')
            ->with(
                $this->callback(
                    function ($arg) use ($mockValue) {
                        $mockValue->key = $arg;
                        return true;
                    }
                ),
                $this->callback(
                    function ($arg) use ($mockValue) {
                        $mockValue->value = $arg;
                        return true;
                    }
                )
            )
            ->will($this->returnValue(true));

        # Memcache::add
        $memcacheMock
            ->method('add')
            ->with(
                $this->callback(
                    function ($arg) use ($mockValue) {
                        if (!$mockValue->key) {
                            $mockValue->key = $arg;
                        }
                        return true;
                    }
                ),
                $this->callback(
                    function ($arg) use ($mockValue) {
                        if (!$mockValue->value) {
                            $mockValue->value = $arg;
                        }
                        return true;
                    }
                )
            )
            ->will(
                $this->returnCallback(
                    function () use ($mockValue) {
                        return !($mockValue->key && $mockValue->value);
                    }
                )
            );

        # Memcache::get
        $memcacheMock
            ->method('get')
            ->with(
                $this->callback(
                    function ($arg) use ($mockValue) {
                        return ($arg == $mockValue->key);
                    }
                )
            )
            ->will(
                $this->returnCallback(
                    function () use ($mockValue) {
                        return $mockValue->value;
                    }
                )
            );

        # Memcache::delete
        $memcacheMock
            ->method('delete')
            ->with(
                $this->callback(
                    function ($arg) use ($mockValue) {
                        if ($arg == $mockValue->key) {
                            $mockValue->value = null;
                        }
                        return true;
                    }
                )
            )
            ->will($this->returnValue(true));

        return $memcacheMock;
    }
}
