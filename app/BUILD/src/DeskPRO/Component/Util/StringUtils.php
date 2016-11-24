<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Component\Util;

/**
 * Utility methods working with strings.
 */
class StringUtils
{
    private function __construct()
    {
    }

    /**
     * @param string $format
     * @param array  $args
     * @param string $default
     *
     * @return string
     */
    public static function format($format, array $args, $default = '__EXCEPTION__')
    {
        $values     = [];
        $realFormat = preg_replace_callback('#%(\((?P<name>.?)\))?#', function (array $match) use (&$values, $args,
            $default) {
            $name = $match['name'];
            if (array_key_exists($args, $name)) {
                $val = $args[$name];
            } else {
                if ($default === '__EXCEPTION__') {
                    throw new \InvalidArgumentException('Unknown named argument');
                }
                $val = $default;
            }

            $values[] = $val;

            return '%';
        }, $format);

        return vsprintf($realFormat, $values);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toSnakeCase($string)
    {
        $string = preg_replace('/(.)([A-Z][a-z]+)/', '$1_$2', $string);
        $string = preg_replace('/(.)([0-9]+)/', '$1_$2', $string);
        $string = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $string);

        return ltrim(strtolower($string), '_');
    }

    /**
     * @param string $string
     * @param bool   $upper
     *
     * @return string
     */
    public static function toCamelCase($string, $upper = true)
    {
        $result = implode('', array_map('ucfirst', explode('_', $string)));

        return $upper ? $result : lcfirst($result);
    }

    /**
     * Check if $needle is at the beginning of $haystack.
     *
     * @param string $needle     The string to search for
     * @param string $haystack   The string to search in
     * @param bool   $ignoreCase True to ignore case
     *
     * @return bool
     */
    public static function startsWith($needle, $haystack, $ignoreCase = false)
    {
        if ($needle === $haystack) {
            return true;
        }

        if ($needle === '' || $haystack === '') {
            return false;
        }

        if ($ignoreCase) {
            return stripos($haystack, $needle) === 0;
        } else {
            return strpos($haystack, $needle) === 0;
        }
    }

    /**
     * Check if $needle is at the end of $haystack.
     *
     * @param string $needle     The string to search for
     * @param string $haystack   The string to search in
     * @param bool   $ignoreCase True to ignore case
     *
     * @return bool
     */
    public static function endsWith($needle, $haystack, $ignoreCase = false)
    {
        if ($needle === $haystack) {
            return true;
        }

        if ($needle === '' || $haystack === '') {
            return false;
        }

        $haystackLen = strlen($haystack);
        $needleLen   = strlen($needle);

        if ($needleLen > $haystackLen) {
            return false;
        }

        return substr_compare($haystack, $needle, $haystackLen - $needleLen, $needleLen, $ignoreCase) === 0;
    }
}
