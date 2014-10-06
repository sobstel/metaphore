<?php
namespace Metaphore;

use Metaphore\Value;

/**
 * Wrapper for values that are not stored by Metaphore.
 *
 * This might be some old values cached by previous system or set
 * not by Metaphore.
 *
 * Ttl is handled by foreign system, so for Metaphore it's always
 * considered fresh (not stale).
 */
class ForeignValue extends Value
{
    /**
     * @param mixed
     */
    public function __construct($result)
    {
        parent::__construct($result, PHP_INT_MAX);
    }

    /**
     * {@inheritdoc}
     */
    public function isStale($nowTimestamp = null)
    {
        return false;
    }
}
