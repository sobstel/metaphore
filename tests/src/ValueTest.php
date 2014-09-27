<?php
namespace Metaphore\Tests;

use Metaphore\Value;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResult()
    {
        $result = 'rojo16';
        $tstamp = 16;

        $value = new Value($result, $tstamp);

        $this->assertEquals($result, $value->getResult());
    }

    public function testRecognizesStaleResult()
    {
        $result = 'zabaleta4';
        $tstamp = 4;

        $value = new Value($result, 3);
        $this->assertTrue($value->isStale($tstamp));

        $value = new Value($result, 4);
        $this->assertFalse($value->isStale($tstamp));

        $value = new Value($result, 5);
        $this->assertFalse($value->isStale($tstamp));
    }

    public function testUsesNowFunctionByDefaultForStaleCheck()
    {
        $result = 'garay2';

        $value = new Value($result, time() + 1);
        $this->assertFalse($value->isStale());
    }
}
