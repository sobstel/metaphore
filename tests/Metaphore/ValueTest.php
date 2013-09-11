<?php
namespace Metaphore;

use PHPUnit_Framework_TestCase;
use Metaphore\Value;

class ValueTest extends PHPUnit_Framework_TestCase
{
    public function testGetResult()
    {
        $result = 'higuain9';
        $value = new Value($result, 1);
        $this->assertEquals($result, $value->getResult());
    }

    public function testRecognizesFreshResult()
    {
        $result = 'higuain9';
        $value = new Value($result, 123);
        $this->assertTrue($value->isFresh(122));
        $this->assertTrue($value->isFresh(123));
    }

    public function testRecognizesStaleResult()
    {
        $result = 'banega20';
        $value = new Value($result, 123);
        $this->assertFalse($value->isFresh(124));
    }

    public function testUsesNowForFreshnessCheckByDefault()
    {
        $result = 'banega20';
        $now = time();

        $value = new Value($result, $now + 1);
        $this->assertTrue($value->isFresh());
    }
}
