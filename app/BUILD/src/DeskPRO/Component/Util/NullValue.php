<?php

namespace DeskPRO\Component\Util;

/**
 * A special object representing no value when you dont want to use an acutal null.
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
        return $var && $var === self::get();
    }
}
