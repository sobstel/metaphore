<?php
namespace Metaphore;

/**
 * Cache value object (stored in cache store).
 */
class Value
{
    /*** @var mixed */
    protected $result;

    /*** @var int */
    protected $expirationTimestamp;

    /**
     * @param mixed
     * @param int
     */
    public function __construct($result, $expirationTimestamp)
    {
        $this->result = $result;
        $this->expirationTimestamp = $expirationTimestamp;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param int (optional)
     * @param bool
     */
    public function isStale($nowTimestamp = null)
    {
        if (!$nowTimestamp) {
            $nowTimestamp = time();
        }

        return ($nowTimestamp > $this->expirationTimestamp);
    }

    /**
     * @return bool
     */
    public function isFalse()
    {
        return ($this->getResult() === false);
    }

    public function __toString()
    {
        return $this->getResult();
    }
}
