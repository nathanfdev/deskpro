<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

/**
 * Utility methods used with arrays/collections as maps (i.e., key-value pairs).
 *
 * Note that most of these methods can accept a Traversable but will return an array.
 */
class MapUtils
{
    private function __construct()
    {
    }

    /**
     * Given an array where each item is an array of [key, value] pairs,
     * create a new array keyed by the key where the value is the value.
     *
     * @param \Traversable|array $array   The array to work on
     * @param int                $key_idx
     * @param int                $val_idx
     *
     * @return array
     */
    public static function arrayMapFromPairs($array, $key_idx = 0, $val_idx = 0)
    {
        $return = [];

        foreach ($array as $row) {
            if (array_key_exists($key_idx, $row) && array_key_exists($val_idx, $row)) {
                $return[$row[$key_idx]] = $row[$val_idx];
            }
        }

        return $return;
    }

    /**
     * Remove all falsey values from an array.
     *
     * @param \Traversable|array $array The array to work on
     *
     * @return array
     */
    public static function filterOutFalsey($array)
    {
        $new = [];

        foreach ($array as $k => $v) {
            if ($v) {
                $new[$k] = $v;
            }
        }

        return $new;
    }

    /**
     * @param             $array
     * @param array|mixed $values Values to remove
     * @param bool        $strict Strict checking on $values
     *
     * @return array
     */
    public static function filterOutValues($array, $values, $strict = true)
    {
        $new = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($array as $k => $v) {
            if (!in_array($v, $values, $strict)) {
                $new[$k] = $v;
            }
        }

        return $new;
    }

    /**
     * Creates a new array with $key=>$value first, and then the rest of $array after.
     *
     * @param \Traversable|array $array
     * @param                    $key
     * @param                    $val
     *
     * @return array
     */
    public static function prependItem($array, $key, $val)
    {
        $new = [$key => $val];

        foreach ($array as $k => $v) {
            if (!isset($new[$k])) {
                $new[$k] = $v;
            }
        }

        return $new;
    }

    /**
     * Create a new array and key it by using a callback on each item of the input array.
     *
     * @param \Traversable|array $array
     * @param callable           $fn
     *
     * @return array
     */
    public static function rekeyByFn($array, $fn)
    {
        $new = [];

        foreach ($array as $k => $v) {
            $set_k = call_user_func($fn, $v, $k);
            if ($set_k !== null) {
                if (is_array($set_k)) {
                    $new = self::setIn($new, $set_k, $v);
                } else {
                    $new[$set_k] = $v;
                }
            }
        }

        return $new;
    }

    /**
     * Create a new array and key it by using the specified key in the input array.
     *
     * @param \Traversable|array $array
     * @param string             $key
     *
     * @return array
     */
    public static function rekeyByKey($array, $key)
    {
        $new = [];

        foreach ($array as $v) {
            if (array_key_exists($key, $v)) {
                $new[$v[$key]] = $v;
            }
        }

        return $new;
    }

    /**
     * Create a new array and key it by using the specified property in the input array.
     *
     * @param \Traversable|array $array
     * @param string             $key
     *
     * @return array
     */
    public static function rekeyByProperty($array, $key)
    {
        $new = [];

        foreach ($array as $v) {
            if (isset($v->$key)) {
                $new[$v->$key] = $v;
            }
        }

        return $new;
    }

    /**
     * Create a new array and key it by using the specified getter method in the input array.
     *
     * @param \Traversable|array $array
     * @param string             $getter
     *
     * @return array
     */
    public static function rekeyByGetter($array, $getter)
    {
        $new = [];

        foreach ($array as $v) {
            $new[$v->$getter()] = $v;
        }

        return $new;
    }

    /**
     * Calls your function on each element of an array. Your function will be passed the key and value,
     * and you MUST return an array of [key, value] to add to the resulting array.
     *
     * @param \Traversable|array $array
     * @param callable           $fn    Your function: fn($key, $value) -> [$key, $value]
     *
     * @return array
     */
    public static function map($array, $fn)
    {
        $return = [];

        foreach ($array as $k => $v) {
            $user_return = call_user_func($fn, $k, $v);
            if (!is_array($user_return) || !array_key_exists(0, $user_return) || !array_key_exists(1, $user_return)) {
                throw new \InvalidArgumentException('Invalid return value');
            }

            if (is_array($user_return[0])) {
                $return = self::setIn($return, $user_return[0], $user_return[1]);
            } else {
                $return[$user_return[0]] = $user_return[1];
            }
        }

        return $return;
    }

    /**
     * Calls your function on each element of an array. Your function will be passed the key and value,
     * and return a new value to add the resulting list.
     *
     * @param \Traversable|array $array
     * @param callable           $fn    Your function: fn($key, $value) -> mixed
     *
     * @return array
     */
    public static function mapToList($array, $fn)
    {
        $return = [];

        foreach ($array as $k => $v) {
            $return[] = call_user_func($fn, $k, $v);
        }

        return $return;
    }

    /**
     * Calls your function on each element of an array. Your function will be passed the key and value,
     * and you must return the new value. It will be saved in a new array using the original key.
     *
     * @param \Traversable|array $array
     * @param callable           $fn    Your function: fn($key, $value) -> $value
     *
     * @return array
     */
    public static function mapValues($array, $fn)
    {
        $return = [];

        foreach ($array as $k => $v) {
            $return[$k] = call_user_func($fn, $k, $v);
        }

        return $return;
    }

    /**
     * Like array_map except your function is called with $key and $value as params.
     *
     * @param \Traversable|array $array
     * @param callable           $fn
     *
     * @return array
     */
    public static function filter($array, $fn)
    {
        $return = [];

        foreach ($array as $k => $v) {
            $user_return = call_user_func($fn, $k, $v);
            if ($user_return === true) {
                $return[$k] = $v;
            }
        }

        return $return;
    }

    /**
     * Get a deep value of an array.
     *
     * @param \Traversable|array        $array
     * @param \Traversable|array|string $path    An array of keys, or a string where keys are separated by dots
     * @param mixed                     $default
     *
     * @return mixed
     */
    public static function getIn($array, $path, $default = null)
    {
        if (is_string($path)) {
            $path = explode('.', trim(trim($path, '.')));
        }

        while (($key = array_shift($path)) !== null) {
            if (!array_key_exists($key, $array)) {
                return $default;
            }

            $array = $array[$key];
        }

        return $array;
    }

    /**
     * Creates a copy of $array and returns it with $path set to $value.
     *
     * Note that if you attempt to set a deep key and the element in the middle of the
     * path is not an array, then a RuntimeException will be raised.
     *
     * @param \Traversable|array        $array
     * @param \Traversable|array|string $path  An array of keys, or a string where keys are separated by dots
     * @param mixed                     $value
     *
     * @return array
     */
    public static function setIn($array, $path, $value)
    {
        if (is_string($path)) {
            $path = explode('.', trim(trim($path, '.')));
        }

        $fullPath = $path;
        $lastPath = array_pop($path);

        $newArray  = $array;
        $arrayPart = &$newArray;

        while (($key = array_shift($path)) !== null) {
            if (!array_key_exists($key, $arrayPart)) {
                $arrayPart[$key] = [];
            }

            $arrayPart = &$arrayPart[$key];

            if (!is_array($arrayPart) && !$arrayPart instanceof \ArrayAccess) {
                throw new \RuntimeException('A value within this path is not an array: '.implode('.', $fullPath));
            }
        }

        $arrayPart[$lastPath] = $value;

        return $newArray;
    }

    /**
     * Returns a map containing all entries from arary1 that are not present or are different from array2.
     *
     * Note that the order does not matter.
     *
     * @param \Traversable|array $array1
     * @param \ArrayAccess|array $array2
     * @param string|callback    $cmp    The comparison technique. Can be '==', '===' or a custom callback
     *
     * @return array
     */
    public static function recursiveDiff($array1, $array2, $cmp = '===')
    {
        $diff = [];

        if (!TypeUtils::isTraversable($array1)) {
            throw new \InvalidArgumentException('$array1 is expected to be traversable');
        }
        if (!TypeUtils::isArrayLike($array2)) {
            throw new \InvalidArgumentException('$array2 is expected to be an array-like value');
        }

        foreach ($array1 as $k => $v) {
            if (TypeUtils::isTraversable($v)) {
                if (!isset($array2[$k]) || !TypeUtils::isTraversable($array2[$k])) {
                    $diff[$k] = $v;
                } else {
                    $subDiff = self::recursiveDiff($v, $array2[$k]);
                    if (!empty($subDiff)) {
                        $diff[$k] = $subDiff;
                    }
                }
            } elseif (!array_key_exists($k, $array2)) {
                $diff[$k] = $v;
            } else {
                switch ($cmp) {
                    case '==':
                        $isSame = $v == $array2[$k];
                        break;
                    case '===':
                        $isSame = $v === $array2[$k];
                        break;
                    default:
                        $isSame = $cmp($v, $array2[$k]);
                }
                if (!$isSame) {
                    $diff[$k] = $v;
                }
            }
        }

        return $diff;
    }

    /**
     * Sorts a map using the return value of $fn(item).
     *
     * The return value of an item is typically an integer. If it is not,
     * we will try to handle it th ebest we can (e.g. true/false, nulls, objects with toString, etc).
     *
     * @param \Traversable|array $array
     * @param callable           $fn      Accepts key, value
     * @param bool               $reverse
     *
     * @return array
     */
    public static function sortByFnValue($array, $fn, $reverse = false)
    {
        $procType = function ($val) {
            $type = strtolower(gettype($val));

            switch ($type) {
                case $val instanceof \Countable:
                    return count($val);
                case 'null':
                    return 2;
                case 'boolean':
                    return $val ? -1 : 1;
                case 'array':
                    return count($val);
                case 'object':
                    if (method_exists($val, '__toString')) {
                        return $val->__toString();
                    } else {
                        return 1;
                    }
                case 'integer':
                case 'double':
                case 'float':
                    return $val;
                case 'resource':
                    return 0;
                default:
                    return 0;
            }
        };

        $sortedKeys = array_keys($array);
        usort($sortedKeys, function ($a, $b) use ($fn, $procType, $reverse, $array) {
            $aVal = $procType($fn($a, $array[$a]));
            $bVal = $procType($fn($b, $array[$b]));

            if ($aVal === $bVal) {
                $order = 0;
            } elseif (is_string($aVal) && is_string($bVal)) {
                $order = strcmp($aVal, $bVal);
            } else {
                $order = $aVal < $bVal ? -1 : 1;
            }

            if ($reverse) {
                $order = $order * -1;
            }

            return $order;
        });

        $array2 = [];
        foreach ($sortedKeys as $k) {
            $array2[$k] = $array[$k];
        }

        return $array2;
    }

    /**
     * @param \Traversable|array $array
     *
     * @return mixed
     */
    public static function first($array)
    {
        return array_values($array)[0];
    }

    /**
     * @param \Traversable|array $array
     *
     * @return mixed
     */
    public static function last($array)
    {
        $vals = array_values($array);
        $len  = count($vals);

        return $vals[$len - 1];
    }

    /**
     * @param \Traversable|array $array
     *
     * @return mixed
     */
    public static function firstKey($array)
    {
        return array_keys($array)[0];
    }

    /**
     * @param \Traversable|array $array
     *
     * @return mixed
     */
    public static function lastKey($array)
    {
        $vals = array_keys($array);
        $len  = count($vals);

        return $vals[$len - 1];
    }
}
