<?php

namespace DeskPRO\Component\Util;

class UnserializeUtil
{
    const ALLOW_ALL  = 22000;
    const ALLOW_NONE = 22001;

    /**
     * Unserialize a serialized string that is expected to be an array.
     *
     * @param string $str
     *
     * @return array
     */
    public static function unserializeArray($str)
    {
        $ret = self::safeUnserialize($str, self::ALLOW_NONE);
        if ($ret === null) {
            throw new \DomainException('failed to unserialize (expected array, got null)');
        }
        if (!is_array($ret)) {
            throw new \UnexpectedValueException('failed to unserialize (expected array)');
        }

        return $ret;
    }

    /**
     * Unserialize a serialized string that is expected to be a scalar.
     *
     * @param string $str
     *
     * @return mixed
     */
    public static function unserializeScalar($str)
    {
        $ret = self::safeUnserialize($str, self::ALLOW_NONE);
        if ($ret === null) {
            throw new \DomainException('failed to unserialize (expected scalar, got null)');
        }
        if (!is_scalar($ret)) {
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
     *
     * @return int
     */
    public static function unserializeString($str)
    {
        $ret = self::safeUnserialize($str, self::ALLOW_NONE);
        if ($ret === null) {
            throw new \DomainException('failed to unserialize (expected string, got null)');
        }
        if (!is_scalar($ret)) {
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
    public static function unserializeInteger($str)
    {
        $ret = self::safeUnserialize($str, self::ALLOW_NONE);
        if ($ret === null) {
            throw new \DomainException('failed to unserialize (expected integer, got null)');
        }
        if (!is_int($ret) && TypeUtils::isIntLike($ret)) {
            $ret = (int) $ret;
        }
        if (!is_int($ret)) {
            throw new \UnexpectedValueException('failed to unserialize (expected integer)');
        }

        return $ret;
    }

    /**
     * @param string         $str            The serialized string
     * @param array|bool|int $allowedClasses Array of classes, or ObjUtils::ALLOW_ALL, ObjUtils::ALLOW_NONE
     *
     * @return mixed
     */
    public static function safeUnserialize($str, $allowedClasses)
    {
        if ($allowedClasses === self::ALLOW_ALL) {
            $allowedClasses = true;
        } elseif ($allowedClasses === self::ALLOW_NONE) {
            $allowedClasses = false;
        }

        if (!is_array($allowedClasses) && $allowedClasses !== true && $allowedClasses !== false) {
            throw new \InvalidArgumentException('allowedClasses option should be array or boolean');
        }

        if (false && version_compare(phpversion(), '7.0.0', '>=')) {
            $ret = unserialize($str, [
                'allowed_classes' => $allowedClasses,
            ]);

            if ($ret instanceof \__PHP_Incomplete_Class_Name) {
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
        if ($allowedClasses === true) {
            return unserialize($str);
        }

        if ($allowedClasses === false) {
            $allowedClasses = [];
        }

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
