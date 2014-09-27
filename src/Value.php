<?php
namespace Metaphore;

class Value
{
    protected $result;
    protected $expirationTimestamp;

    public function __construct($result, $expirationTimestamp)
    {
        $this->result = $result;
        $this->expirationTimestamp = $expirationTimestamp;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function isStale($nowTimestamp = null)
    {
        if (!$nowTimestamp) {
            $nowTimestamp = time();
        }

        return ($nowTimestamp > $this->expirationTimestamp);
    }

    public function __toString()
    {
        return $this->getResult();
    }
}
