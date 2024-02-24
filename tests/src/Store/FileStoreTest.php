<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\FileStore;

class FileStoreTest extends AbstractStoreTestCase
{
    public function setUp(): void
    {
        $this->store = new FileStore;
    }

    public function tearDown(): void
    {
        $this->store = null;
    }
}
