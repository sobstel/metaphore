<?php
namespace Metaphore;

use Metaphore\Value;
use Metaphore\ForeignValue;
use Metaphore\Store\ValueStoreInterface;
use Metaphore\Store\LockStoreInterface;
use Metaphore\LockManager;
use Metaphore\Exception;
use Metaphore\Ttl;
use Metaphore\NoStaleCacheEvent;

/**
 * Main class handling cache.
 */
class Cache
{
    /*** @var \Metaphore\Store\ValueStoreInterface */
    protected $valueStore;

    /*** @var \Metaphore\LockManager */
    protected $lockManager;

    /*** @var callable */
    protected $onNoStaleCacheCallable;

    /**
     * @param \Metaphore\Store\ValueStoreInterface
     * @param \Metaphore\Store\LockStoreInterface
     * @throws \Metaphore\Exception When value store cannot be used as lock store by default.
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
     * @param callable
     */
    public function cache($key, callable $cachedCallable, $ttl, callable $onNoStaleCacheCallable = null)
    {
        $value = $this->getValue($key);

        if ($value->hasResult() && !$value->isStale()) {
            return $value->getResult();
        }

        if (!($ttl instanceof Ttl)) {
            $ttl = new Ttl($ttl);
        }

        $lock_acquired = $this->lockManager->acquire($key, $ttl->getLockTtl());

        if (!$lock_acquired) {
            if ($value->hasResult()) { // serve stale if present
                return $value->getResult();
            }

            if (!$onNoStaleCacheCallable) {
                $onNoStaleCacheCallable = $this->onNoStaleCacheCallable;
            }

            if ($onNoStaleCacheCallable !== null) {
                $event = new NoStaleCacheEvent($this, $key, $cachedCallable, $ttl);

                call_user_func($onNoStaleCacheCallable, $event);

                if ($event->hasResult()) {
                    return $event->getResult();
                }
            }
        }

        $result = call_user_func($cachedCallable);

        $this->setResult($key, $result, $ttl);

        if ($lock_acquired) {
            $this->lockManager->release($key);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function delete($key)
    {
        return $this->valueStore->delete($key);
    }

    /**
     * @return Value
     */
    public function getValue($key)
    {
        $value = $this->valueStore->get($key);

        if (!($value instanceof Value)) {
            $value = new ForeignValue($value);
        }

        return $value;
    }

    /**
     * Sets result. Does not use anti-dogpile-effect mechanism. Use cache() instead for this.
     *
     * @param string
     * @param mixed
     * @param int|\Metaphore\Ttl
     */
    public function setResult($key, $result, $ttl)
    {
        if (!($ttl instanceof Ttl)) {
            $ttl = new Ttl($ttl);
        }

        $expirationTimestamp = time() + $ttl->getTtl();
        $value = new Value($result, $expirationTimestamp);

        $this->valueStore->set($key, $value, $ttl->getRealTtl());
    }

    /**
     * @param callable
     */
    public function onNoStaleCache(callable $onNoStaleCacheCallable)
    {
        $this->onNoStaleCacheCallable = $onNoStaleCacheCallable;
    }

    /**
     * @return ValueStoreInterface
     */
    public function getValueStore()
    {
        return $this->valueStore;
    }

    /**
     * @return LockManager
     */
    public function getLockManager()
    {
        return $this->lockManager;
    }
}
