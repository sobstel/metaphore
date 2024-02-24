<?php
namespace Metaphore\Tests\Store;

use Metaphore\Store\MockStore;

class MockStoreTest extends AbstractStoreTestCase
{
    public function setUp(): void
    {
        $this->store = new MockStore();
    }

    public function tearDown(): void
    {
        $this->store = null;
    }

    public function testDeleteReturnsFalseIfKeyNotExists()
    {
        $result = $this->store->delete('banega');

        $this->assertFalse($result);
    }
}
