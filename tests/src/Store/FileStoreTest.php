<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\FileStore;

class FileStoreTest extends AbstractStoreTest
{
    public function setUp()
    {
        $this->store = new FileStore;
    }

    public function tearDown()
    {
        $this->store = null;
    }
}
