<?php

namespace Metaphore\Tests\Store;

use Metaphore\Store\PredisStore;

class PredisStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $this->store = new PredisStore();
    }

    public function tearDown()
    {
        $this->store = null;
    }
}