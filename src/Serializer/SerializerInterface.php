<?php

namespace Metaphore\Serializer;

interface SerializerInterface
{
    public function serialize($value);
    public function unserialize($str);
}
