<?php

namespace Metaphore\Tests;

use Metaphore\Serializer\NativeSerializer;

class NativeSerializerTest extends \PHPUnit\Framework\TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new NativeSerializer();
    }

    public function testSerialize()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $serialized = $this->serializer->serialize($obj);

        $this->assertEquals($serialized, serialize($obj));
    }

    public function testUnserialize()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';

        $serialized = serialize($obj);
        $unserialized = $this->serializer->unserialize($serialized);

        $this->assertEquals($unserialized, unserialize($serialized));
    }
}
