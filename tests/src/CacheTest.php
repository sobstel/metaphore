<?php
namespace Metaphore\Tests;

use Metaphore\Cache;
use Metaphore\Ttl;
use Metaphore\Store\MockStore;
use Metaphore\NoStaleCacheEvent;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCachesValue()
    {
        $cache = new Cache(new MockStore());

        $key = "gago5";
        $result = "Fernando Rubén Gago plays as a defensive midfielder for Boca Juniors and the Argentine team.";

        $actualResult = $cache->cache($key, $this->createFunc($result), 30);

        $this->assertSame($result, $actualResult);
        $this->assertSame($result, $cache->getValue($key)->getResult());
    }

    public function testServesStaleValueIfOtherProcessIsGeneratingContent()
    {
        $key = "masche14";

        $lockManagerMock = $this->createLockManagerMock();
        $lockManagerMock->method('acquire')->with($this->equalTo($key))->will($this->returnValue(true));
        $lockManagerMock->method('release')->with($this->equalTo($key))->will($this->returnValue(true));

        $cache = new Cache(new MockStore, $lockManagerMock);

        $resultNew = "Javier Alejandro Mascherano plays for FC Barcelona in La Liga and the Argentina national team.";
        $resultStale = "Javier Mascherano";

        $retVal = $cache->cache($key, $this->createFunc($resultStale), -1);
        $this->assertSame($retVal, $resultStale);

        // simulate lock (other process generating content)
        $cache->getLockManager()->acquire($key, 30);

        // try to cache new (but stale value should be returned as lock is acquired earlier)
        $ret_val = $cache->cache($key, $this->createFunc($resultNew), -1);

        $this->assertSame($retVal, $resultStale);

        // release lock and try to cache new value again
        $cache->getLockManager()->release($key);

        $retVal = $cache->cache($key, $this->createFunc($resultNew), -1);

        $this->assertSame($retVal, $resultNew);
    }

    public function testCallsCallableIfNotStaleContentAvailableToServe()
    {
        $cache = new Cache(new MockStore());

        $noStaleCacheEvent = false;

        $cache->onNoStaleCache(function (NoStaleCacheEvent $event) use (&$noStaleCacheEvent) {
            $noStaleCacheEvent = $event;
        });

        $key = 'maxi11';
        $value = 'Maxi\s goal with Mexico in 2006 was truly brilliant.';
        $ttl = 30;

        // simulate lock (other process generating content)
        $cache->getLockManager()->acquire($key, 30);

        $cache->cache($key, $this->createFunc($value), $ttl);

        $this->assertNotFalse($noStaleCacheEvent, 'NO_STALE_CACHE event not called');
        $this->assertSame($key, $noStaleCacheEvent->getKey());
        $this->assertSame($ttl, (int)(string)$noStaleCacheEvent->getTtl());
    }

    public function testCallableCanReturnDifferentValueIfNotStaleContentAvailableToServe()
    {
        $cache = new Cache(new MockStore());

        $customValue = 'custom_value';

        $cache->onNoStaleCache(function (NoStaleCacheEvent $event) use ($customValue) {
            $event->setResult($customValue);
        });

        $key = 'lamela';
        $value = 'Tottenham Rabona';
        $ttl = 30;

        // simulate lock (other process generating content)
        $cache->getLockManager()->acquire($key, 30);

        $result = $cache->cache($key, $this->createFunc($value), $ttl);

        $this->assertSame($customValue, $result);
    }

    public function testGetReturnsResultEvenIfNoMetaphoreValueObjectStored()
    {
        $cache = new Cache(new MockStore());

        $key = 'dimaria7';
        $value = 'Man Utd, not Real';

        $cache->setResult($key, $value, new Ttl(30));
        $result = $cache->getValue($key);

        $this->assertTrue($result instanceof \Metaphore\Value);
        $this->assertSame($value, $result->getResult());
    }

    protected function createLockManagerMock()
    {
        return $this->getMockBuilder('Metaphore\LockManager')
            ->setConstructorArgs([new MockStore])
            ->setMethods(['acquire', 'release'])
            ->getMock();
    }

    protected function createFunc($result)
    {
        return (function () use ($result) {
            return $result;
        });
    }
}
