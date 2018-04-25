<?php

namespace DeskPRO\Component\FilterQueryLanguage;

class QueryUtil
{
    /**
     * Escape a string value.
     *
     * @param string $val
     *
     * @return string
     */
    public static function escapeValue($val)
    {
        return str_replace(
            ['"', "'"],
            ['""', '""'],
            $val
        );
    }

    /**
     * Quote a value for use in a query string.
     *
     * @param string $val
     *
     * @return string
     */
    public static function quoteValue($val)
    {
        return '"'.self::escapeValue($val).'"';
    }
}
