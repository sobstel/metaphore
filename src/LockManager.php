<?php
namespace Metaphore;

use Metaphore\Store\LockStoreInterface;

class LockManager
{
    protected $lockStore;

    protected $acquiredLocks = [];

    public function __construct(LockStoreInterface $lockStore)
    {
        $this->lockStore = $lockStore;
    }

    public function __destruct()
    {
        // release lock that have been acquired but not released for some reason
        foreach ($this->acquiredLocks as $key => $true) {
            $this->release($key);
            unset($this->acquiredLocks[$key]);
        }
    }

    protected function prepareLockKey($key)
    {
        return sprintf('%s.lock', $key);
    }

    public function acquire($key, $grace_ttl)
    {
        // educated guess (remove lock early enough so if anything goes wrong
        // with first process, another one can pick up)
        // SMELL: a bit problematic, why $grace_ttl/2 ???
        $lock_ttl = max(1, (int)($grace_ttl/2));

        $result = $this->lockStore->add($this->prepareLockKey($key), 1, $lock_ttl);

        if ($result) {
            $this->acquiredLocks[$key] = true;
        }

        return $result;
    }

    public function release($key)
    {
        $this->lockStore->delete($this->prepareLockKey($key));

        if (isset($this->acquiredLocks[$key])) {
            unset($this->acquiredLocks[$key]);
        }
    }

    public function getLockStore()
    {
        return $this->lockStore;
    }
}
