<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * A special object representing no value.
 * Used in situations where 'null' might be an acceptable value. E.g., default return value in Arrays::keyAsPath().
 *
 * @static
 */
class NullValue
{
    private function __construct()
    {
    }

    /**
     * @return NullValue
     */
    public static function get()
    {
        static $inst = null;

        if ($inst === null) {
            $inst = new self();
        }

        return $inst;
    }

    /**
     * Check if a variable is a NullValue.
     *
     * @param mixed $var
     *
     * @return bool
     */
    public static function is($var)
    {
        return $var === self::get();
    }
}
