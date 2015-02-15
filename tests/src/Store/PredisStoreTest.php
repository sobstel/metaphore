<?php

namespace Metaphore\Tests\Store;

use Metaphore\Store\PredisStore;

use Predis\Client;

class PredisStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $this->store = new PredisStore(new Client());
    }

    public function tearDown()
    {
        $this->store = null;
    }
}