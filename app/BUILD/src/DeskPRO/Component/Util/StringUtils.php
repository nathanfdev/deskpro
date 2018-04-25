<?php

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
     * Remove a prefix from the string. If the string does not include the prefix
     * (i.e. it fails startsWith() check), then null is returned instead.
     *
     * <code>
     * $a = 'foo.bar.baz';
     * echo StringUtils::removeFromStart('foo.bar.', $a); // => baz
     *
     * $b = 'foo.bar.baz';
     * echo StringUtils::removeFromStart('loo.bar.', $b); // => null
     * </code>
     *
     * @param string $needle
     * @param string $haystack
     * @param bool   $ignoreCase
     *
     * @return string
     */
    public static function removeFromStart($needle, $haystack, $ignoreCase = false)
    {
        if (!self::startsWith($needle, $haystack, $ignoreCase)) {
            return null;
        }

        return substr($haystack, strlen($needle));
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
