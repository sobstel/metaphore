<?php
namespace Metaphore;

use Metaphore\Cache;
use Metaphore\Store\Mock as MockStore;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /*** @var \Metaphore\Cacche */
    protected $cache;

    public function setUp()
    {
        $this->cache = new Cache(new MockStore());
    }

    public function testCachesValue()
    {
        $key = "gago5";
        $result = "Fernando Rubén Gago plays as a defensive midfielder for Boca Juniors and the Argentine national team.";

        $actual_result = $this->cache->cache($key, $this->createFunc($result), 30);

        $this->assertSame($actual_result, $result);
        $this->assertSame($this->cache->get($key), $result);
    }

    public function testServesStaleValueIfOtherProcessIsGeneratingContent()
    {
        $key = "masche14";
        $result_new = "Javier Alejandro Mascherano plays for FC Barcelona in La Liga and the Argentina national team, as a central defender or defensive midfielder.";
        $result_stale = "Javier Mascherano";

        $ret_val = $this->cache->cache($key, $this->createFunc($result_stale), -1);

        $this->assertSame($ret_val, $result_stale);

        // simulate lock (other process generating content)
        $acquire_lock_method = new \ReflectionMethod($this->cache, 'acquireLock');
        $acquire_lock_method->setAccessible(true);
        $acquire_lock_method->invoke($this->cache, $key, 30);

        // try to cache new (but stale value should be returned as lock is acquired earlier)
        $ret_val = $this->cache->cache($key, $this->createFunc($result_new), -1);

        $this->assertSame($ret_val, $result_stale);

        // release lock and try to cache new value again
        $release_lock_method = new \ReflectionMethod($this->cache, 'releaseLock');
        $release_lock_method->setAccessible(true);
        $release_lock_method->invoke($this->cache, $key);

        $ret_val = $this->cache->cache($key, $this->createFunc($result_new), -1);

        $this->assertSame($ret_val, $result_new);
    }

    protected function createFunc($result)
    {
        return function() use ($result) { return $result; };
    }
}
