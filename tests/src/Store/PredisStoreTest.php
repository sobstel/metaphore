<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\PredisStore;
use Predis\Client;

/**
 * @group notisolated
 * @group redis
 * @group php-predis
 */
class PredisStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $client = new Client();
        $this->store = new PredisStore($client);
    }

    public function tearDown()
    {
        $this->store = null;
    }
}
