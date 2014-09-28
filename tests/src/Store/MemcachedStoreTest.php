<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\MemcachedStore;

class MemcachedStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $this->store = new MemcachedStore($this->createMemcachedMock());
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
        $this->assertTrue($prepared_ttl <= MemcachedStore::MAX_TTL || $prepared_ttl >= time());
    }

    protected function createMemcachedMock()
    {
        // we need values set by set() call to be available for get() call
        $mockValue = new \StdClass;
        $mockValue->key = null;
        $mockValue->value = null;

        $memcachedMock = $this->getMockBuilder('Memcached')
            ->setMethods(['set', 'add', 'get', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        # Memcached::set
        $memcachedMock
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

        # Memcached::add
        $memcachedMock
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

        # Memcached::get
        $memcachedMock
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

        # Memcached::delete
        $memcachedMock
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

        return $memcachedMock;
    }
}
