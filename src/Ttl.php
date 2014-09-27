<?php
namespace Metaphore;

// time-to-live (value object)
class Ttl
{
    const DEFAULT_GRACE_TTL = 60;

    /*** @var int */
    protected $ttl;

    /*** @var int How long to serve stale content while new one is being generated */
    protected $graceTtl;

    /*** @var int */
    protected $lockTtl;

    public function __construct($ttl, $graceTtl = null, $lockTtl = null)
    {
        $this->ttl = (int)$ttl;

        if (isset($graceTtl)) {
            $this->graceTtl = (int)$graceTtl;
        }

        if (isset($lockTtl)) {
            $this->lockTtl = (int)$lockTtl;
        }
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    // how log it's really cached in cache store
    public function getRealTtl()
    {
        // $grace_ttl added, so stale result might be served if needed
        return ($this->getTtl() + $this->getGraceTtl());
    }

    public function getGraceTtl()
    {
        if (!isset($this->graceTtl)) {
            $this->graceTtl = self::DEFAULT_GRACE_TTL;
        }

        return $this->graceTtl;
    }

    public function getLockTtl()
    {
        if (!isset($this->lockTtl)) {
            // educated guess (remove lock early enough so if anything goes wrong
            // with first process, another one can pick up)
            // SMELL: a bit problematic, why $grace_ttl/2 ???
            $this->lockTtl = max(1, (int)($this->getGraceTtl()/2));
        }

        return $this->lockTtl;
    }

    public function __toString()
    {
        return (string)$this->getTtl();
    }
}
