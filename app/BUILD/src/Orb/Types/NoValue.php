<?php

/**
 * Orb.
 *
 * @category Types
 */

namespace Orb\Types;

class NoValue
{
    /**
     * @var NoValue
     */
    private static $inst = null;

    /**
     * @return NoValue
     */
    public static function get()
    {
        if (self::$inst === null) {
            self::$inst = new self();
        }

        return self::$inst;
    }

    /**
     * Check if a value is a NoValue object.
     *
     * @param mixed $v
     *
     * @return bool
     */
    public static function is($v)
    {
        if (self::$inst === null) {
            return false;
        }

        return self::$inst === $v;
    }

    private function __construct()
    {
    }
}
