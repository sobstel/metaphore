<?php
namespace Metaphore;

use Metaphore\Value;
use Metaphore\Store\ValueStoreInterface;
use Metaphore\Ttl;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class Cache
{
    /*** @var \Metaphore\Store\ValueStoreInterface */
    protected $valueStore;

    /*** @var \Metaphore\LockManager */
    protected $lockManager;

    protected $eventDispatcher;

    /**
     * @param \Metaphore\Store\ValueStoreInterface
     * @param \Metaphore\Store\LockStoreInterface
     */
    public function __construct(ValueStoreInterface $valueStore, LockManager $lockManager = null)
    {
        $this->valueStore = $valueStore;

        if (!$lockManager) {
            $lockManager = new LockManager($valueStore);
        }
        $this->lockManager = $lockManager;

        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * Cache specified closure/method/function for specified time.
     *
     * As a third argument - instead of integer - you can pass Ttt object to 
     * define grace tll and lock ttl (both optional).
     *
     * @param string
     * @param callable
     * @param int|\Metaphore\Ttl 
     */
    public function cache($key, callable $callable, $ttl)
    {
        $value = $this->valueStore->get($key);

        if (!$this->isValue($value)) { // if not proper Metaphore\Value object...
            if ($value) { // ...return current result/value if present
                return $value;
            }
            // ...create fake expired value object for convenience (to avoid additional checks later)
            $value = new Value($value, -1);
        }

        if (!$value->isStale()) {
            return $value->getResult();
        }

        if (!($ttl instanceof Ttl)) {
            $ttl = new Ttl($ttl);
        }

        $lock_acquired = $this->lockManager->acquire($key, $ttl->getLockTtl());

        if (!$lock_acquired) {
            if ($value->getResult() !== false) { // serve stale if present
                return $value->getResult();
            }

            $this->getEventDispatcher()->dispatch(
                'NO_STALE_CACHE',
                new GenericEvent(
                    $this,
                    [
                        'key' => $key,
                        'callable' => $callable,
                        'ttl' => $ttl,
                        'value' => $value,
                    ]
                )
            );
        }

        $result = call_user_func($callable);

        $expiration_timestamp = time() + $ttl->getTtl();
        $value = new Value($result, $expiration_timestamp);

        $this->valueStore->set($key, $value, $ttl->getRealTtl());

        if ($lock_acquired) {
            $this->lockManager->release($key);
        }

        return $result;
    }

    public function get($key)
    {
        $value = $this->valueStore->get($key);

        if ($this->isValue($value)) {
            return $value->getResult();
        }

        return $value;
    }

    public function delete($key)
    {
        $this->valueStore->delete($key);
    }

    public function getValueStore()
    {
        return $this->valueStore;
    }

    public function getLockManager()
    {
        return $this->lockManager;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    protected function isValue($value)
    {
        return ($value !== false && $value instanceof \Metaphore\Value);
    }
}
