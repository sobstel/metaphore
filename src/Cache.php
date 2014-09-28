<?php
namespace Metaphore;

use Metaphore\Value;
use Metaphore\ForeignValue;
use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Metaphore\LockManager;
use Metaphore\Exception;
use Metaphore\Ttl;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Main class handling cache.
 */
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
            if (!($valueStore instanceof LockStoreInterface)) {
                throw new Exception(
                    sprintf('%s does not implement LockStoreInterface. ', get_class($valueStore)).
                    'Please provide lock manager or value store that\'s compatible with lock store. '
                );
            }

            $lockManager = new LockManager($valueStore);
        }
        $this->lockManager = $lockManager;

        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * Caches specified closure/method/function for specified time.
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
        $value = $this->get($key);

        if (!$value->isFalse() && !$value->isStale()) {
            return $value->getResult();
        }

        if (!($ttl instanceof Ttl)) {
            $ttl = new Ttl($ttl);
        }

        $lock_acquired = $this->lockManager->acquire($key, $ttl->getLockTtl());

        if (!$lock_acquired) {
            if (!$value->isFalse()) { // serve stale if present
                return $value->getResult();
            }

            $this->dispatchNoStaleCacheEvent($key, $callable, $ttl);
        }

        $result = call_user_func($callable);

        $this->setResult($key, $result, $ttl);

        if ($lock_acquired) {
            $this->lockManager->release($key);
        }

        return $result;
    }

    /**
     * @return Value
     */
    public function get($key)
    {
        $value = $this->valueStore->get($key);

        if (!($value instanceof Value)) {
            $value = new ForeignValue($value);
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function delete($key)
    {
        return $this->valueStore->delete($key);
    }

    /**
     * Sets result. Does not use anti-dogpile-effect mechanism. Use cache() instead for this.
     */
    public function setResult($key, $result, Ttl $ttl)
    {
        $expirationTimestamp = time() + $ttl->getTtl();
        $value = new Value($result, $expirationTimestamp);

        $this->valueStore->set($key, $value, $ttl->getRealTtl());
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

    protected function dispatchNoStaleCacheEvent($key, $callable, $ttl)
    {
        $this->getEventDispatcher()->dispatch(
            'NO_STALE_CACHE',
            new GenericEvent(
                $this,
                [
                    'key' => $key,
                    'callable' => $callable,
                    'ttl' => $ttl,
                ]
            )
        );
    }
}
