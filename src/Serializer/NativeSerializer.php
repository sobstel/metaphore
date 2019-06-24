<?php

namespace Metaphore\Serializer;

class NativeSerializer implements SerializerInterface
{
    public function serialize($value)
    {
        return serialize($value);
    }

    public function unserialize($str)
    {
        return unserialize($str);
    }
}
