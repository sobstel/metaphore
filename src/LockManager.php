<?php
namespace Metaphore;

use Metaphore\Store\LockStoreInterface;

/**
 * Manages locks (acquiring and releasing).
 */
class LockManager
{
    /*** @var LockStoreInterface */
    protected $lockStore;

    protected $acquiredLocks = [];

    /**
     * @param LockStoreInterface
     */
    public function __construct(LockStoreInterface $lockStore)
    {
        $this->lockStore = $lockStore;
    }

    public function __destruct()
    {
        // release locks that have been acquired but not released for some reason
        foreach ($this->acquiredLocks as $key => $true) {
            $this->release($key);
        }
    }

    /**
     * @param string
     * @param int
     * @return bool
     */
    public function acquire($key, $lockTtl)
    {
        $result = $this->lockStore->add($this->prepareLockKey($key), 1, $lockTtl);

        if ($result) {
            $this->acquiredLocks[$key] = true;
        }

        return $result;
    }

    /**
     * @param string
     * @return bool
     */
    public function release($key)
    {
        $result = $this->lockStore->delete($this->prepareLockKey($key));

        if (isset($this->acquiredLocks[$key])) {
            unset($this->acquiredLocks[$key]);
        }

        return $result;
    }

    /**
     * @return LockStoreInterface
     */
    public function getLockStore()
    {
        return $this->lockStore;
    }

    protected function prepareLockKey($key)
    {
        return sprintf('%s.lock', $key);
    }
}
