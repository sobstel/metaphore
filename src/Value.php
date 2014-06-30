<?php
namespace Metaphore;

class Value
{
    protected $result;
    protected $expiration_timestamp;

    public function __construct($result, $expiration_timestamp)
    {
        $this->result = $result;
        $this->expiration_timestamp = $expiration_timestamp;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function isStale($now_timestamp = null)
    {
        if (!$now_timestamp) {
            $now_timestamp = time();
        }

        return ($now_timestamp > $this->expiration_timestamp);
    }
}
