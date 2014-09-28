<?php
namespace Metaphore\Tests;

use Metaphore\LockManager;
use Metaphore\Store\MockStore;

class LockManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $lockManager;

    public function setUp()
    {
        $this->lockManager = new LockManager(new MockStore);
    }

    public function tearDown()
    {
        $this->lockManager = null;
    }

    public function testReleaseAndAcquire()
    {
        $key = 'garay2';

        $this->lockManager->acquire($key, 30);

        $result = $this->lockManager->release($key, 30);
        $this->assertTrue($result);

        $result = $this->lockManager->acquire($key, 30);
        $this->assertTrue($result);
    }

    public function testAcquireReturnsFalseForConsecutiveTries()
    {
        $key = 'rojo16';

        $result = $this->lockManager->acquire($key, 30);
        $this->assertTrue($result);

        $result = $this->lockManager->acquire($key, 30);
        $this->assertFalse($result);

        $result = $this->lockManager->acquire($key, 30);
        $this->assertFalse($result);
    }
}
