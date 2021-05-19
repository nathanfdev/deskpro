<?php

namespace DeskPRO\Component\Util;

use xKerman\Restricted;

class UnserializeUtil
{
    const DEFAULT_THROW = '___throw___';

    /**
     * Unserialize a serialized string that is expected to be an array.
     *
     * @param string $str
     * @param mixed $default Default value if unserialize failed
     * @return array
     */
    public static function unserializeArray($str, $default = self::DEFAULT_THROW)
    {
        $ret = self::safeUnserialize($str, $default);
        if ($ret === null) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \DomainException('failed to unserialize (expected array, got null)');
        }
        if (!is_array($ret)) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \UnexpectedValueException('failed to unserialize (expected array)');
        }

        return $ret;
    }

    /**
     * Unserialize a serialized string that is expected to be a scalar.
     *
     * @param string $str
     * @param mixed $default Default value if unserialize failed
     * @return mixed
     */
    public static function unserializeScalar($str, $default = self::DEFAULT_THROW)
    {
        $ret = self::safeUnserialize($str, $default);
        if ($ret === null) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \DomainException('failed to unserialize (expected scalar, got null)');
        }
        if (!is_scalar($ret)) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \UnexpectedValueException('failed to unserialize (expected scalar)');
        }

        return $ret;
    }

    /**
     * Test if a serialized string is null.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isNull($str)
    {
        return trim($str) === 'N;';
    }

    /**
     * Unserialize a serialized string that is expected to be a integer.
     *
     * @param string $str
     * @param mixed $default Default value if unserialize failed
     * @return int
     */
    public static function unserializeString($str, $default = self::DEFAULT_THROW)
    {
        $ret = self::safeUnserialize($str, $default);
        if ($ret === null) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \DomainException('failed to unserialize (expected string, got null)');
        }
        if (!is_scalar($ret)) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \UnexpectedValueException('failed to unserialize (expected string)');
        }

        return (string) $ret;
    }

    /**
     * Unserialize a serialized string that is expected to be a integer.
     *
     * @param string $str
     *
     * @return int
     */
    public static function unserializeInteger($str, $default = self::DEFAULT_THROW)
    {
        $ret = self::safeUnserialize($str, $default);
        if ($ret === null) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \DomainException('failed to unserialize (expected integer, got null)');
        }
        if (!is_int($ret) && TypeUtils::isIntLike($ret)) {
            $ret = (int) $ret;
        }
        if (!is_int($ret)) {
            if ($default !== self::DEFAULT_THROW) {
                return $default;
            }
            throw new \UnexpectedValueException('failed to unserialize (expected integer)');
        }

        return $ret;
    }

    /**
     * This is a safe version of unserialize that will only unserialize arrays and scalars.
     *
     * @param string $str The serialized string*
     * @param mixed $default Default value if unserialize failed
     * @return mixed
     */
    public static function safeUnserialize($str, $default = self::DEFAULT_THROW)
    {
        try {
            return Restricted\unserialize($str);
        } catch (\Exception $e) {
            if ($default === self::DEFAULT_THROW) {
                throw new \UnexpectedValueException('failed to unserialize', 0, $e);
            }

            return $default;
        }
    }

    /**
     * This unserializes a string with the given allowed classes.
     *
     * @param string $str
     * @param array $allowedClasses
     * @return mixed
     */
    public static function unserializeClass($str, array $allowedClasses)
    {
        if (empty($allowedClasses)) {
            throw new \DomainException('failed to unserialize (no allowedClasses)');
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $ret = unserialize($str, [
                'allowed_classes' => $allowedClasses,
            ]);

            if (!is_object($ret)) {
                throw new \UnexpectedValueException('failed to unserialize (did not unserialize to a class)');
            }

            if ($ret instanceof \__PHP_Incomplete_Class || get_class($ret) === '__PHP_Incomplete_Class') {
                throw new \UnexpectedValueException('failed to unserialize (failed allowedClasses)');
            }
        } else {
            $ret = self::unserializePolyfill($str, $allowedClasses);
        }

        return $ret;
    }

    // based off https://github.com/dbrumann/polyfill-unserialize
    private static function unserializePolyfill($str, $allowedClasses)
    {
        $sanitizedSerialized = preg_replace_callback(
            '/(^|;)O:\d+:"([^"]*)":(\d+):{/',
            function ($match) use ($allowedClasses) {
                $completeMatch = (string) $match[0];
                $className = (string) $match[2];

                if (in_array($className, $allowedClasses, true)) {
                    return $completeMatch;
                }
                throw new \UnexpectedValueException('failed to unserialize (failed allowedClasses)');
            },
            $str
        );

        return unserialize($sanitizedSerialized);
    }
}
