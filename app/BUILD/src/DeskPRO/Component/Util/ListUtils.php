<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

use DeskPRO\Component\Collections\ReverseIterator;
use Symfony\Component\Validator\Tests\Fixtures\Countable;

/**
 * Utility methods used with plain arrays/collections (i.e., numerically indexed).
 *
 * If you need to maintain indexes, use MapUtils.
 */
class ListUtils
{
    private function __construct()
    {
    }

    /**
     * Similar with array_filter, but you can use indexes. Use MapUtils::filter if you need to preserve indexes.
     *
     * @param \Traversable|array $array
     * @param callable           $fn    Your function is passed: $fn($value, $index)
     *
     * @return array
     */
    public static function filter($array, $fn)
    {
        $new = [];

        foreach ($array as $k => $v) {
            if ($fn($v, $k)) {
                $new[] = $v;
            }
        }

        return $new;
    }

    /**
     * Remove all falsey values from an array.
     *
     * @param \Traversable|array $array
     *
     * @return array
     */
    public static function filterOutFalsey($array)
    {
        $new = [];

        foreach ($array as $v) {
            if ($v) {
                $new[] = $v;
            }
        }

        return $new;
    }

    /**
     * @param \Traversable|array $array
     * @param array|mixed        $values Values to remove
     * @param bool               $strict Strict checking on $values
     *
     * @return array
     */
    public static function filterOutValues($array, $values, $strict = true)
    {
        $new = [];

        if (!is_array($values) && !($array instanceof \Traversable)) {
            $values = [$values];
        }

        foreach ($array as $v) {
            if (!in_array($v, $values, $strict)) {
                $new[] = $v;
            }
        }

        return $new;
    }

    /**
     * Similar to array_map except this always returns a plain array (i.e. string keys are completely discarded).
     *
     * @param \Traversable|array $array
     * @param callable           $fn
     *
     * @return array
     */
    public static function map($array, $fn)
    {
        $arr = [];

        foreach ($array as $k => $v) {
            $arr[] = $fn($v, $k);
        }

        return $arr;
    }

    /**
     * Calls $fn on each value in $array and any that are not null are returned as part of a new array.
     *
     * @param \Traversable|array $array
     * @param callable           $fn
     *
     * @return array
     */
    public static function filterMap($array, $fn)
    {
        $arr = [];

        foreach ($array as $k => $v) {
            $v2 = $fn($v, $k);
            if ($v2 !== null) {
                $arr[] = $v2;
            }
        }

        return $arr;
    }

    /**
     * Check $array to see if $value exists in it anywhere.
     *
     * @param \Traversable|array $array
     * @param mixed              $value
     * @param bool               $strict
     *
     * @return bool
     */
    public static function contains($array, $value, $strict = true)
    {
        if (is_array($array)) {
            return in_array($value, $array, $strict);
        }

        if ($strict) {
            foreach ($array as $v) {
                if ($v === $value) {
                    return true;
                }
            }
        } else {
            foreach ($array as $v) {
                if ($v == $value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check $array to see if any value of $values exists in it anywhere.
     *
     * @param \Traversable|array $array
     * @param \Traversable|array $values
     * @param bool               $strict
     *
     * @return bool
     */
    public static function containsAny($array, $values, $strict = true)
    {
        if (is_array($array)) {
            foreach ($values as $value) {
                if (in_array($value, $array, $strict)) {
                    return true;
                }
            }

            return false;
        }

        if ($strict) {
            foreach ($values as $value) {
                foreach ($array as $v) {
                    if ($v === $value) {
                        return true;
                    }
                }
            }
        } else {
            foreach ($values as $value) {
                foreach ($array as $v) {
                    if ($v == $value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check $array to see if all values of $values exists in it anywhere.
     *
     * @param \Traversable|array $array
     * @param \Traversable|array $values
     * @param bool               $strict
     *
     * @return bool
     */
    public static function containsAll($array, $values, $strict = true)
    {
        $all = false;

        if ($strict) {
            foreach ($values as $value) {
                $has = false;
                foreach ($array as $v) {
                    if ($v === $value) {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    return false;
                } else {
                    $all = true;
                }
            }
        } else {
            foreach ($values as $value) {
                $has = false;
                foreach ($array as $v) {
                    if ($v == $value) {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    return false;
                } else {
                    $all = true;
                }
            }
        }

        return $all;
    }

    /**
     * Given an array containing other arrays, append them to eachother to create a big final array.
     *
     * @param \Traversable|array $array
     *
     * @return array
     */
    public static function appendListOfLists($array)
    {
        $all = [];

        foreach ($array as $sub_array) {
            if (!is_array($sub_array) && !$sub_array instanceof \Traversable) {
                throw new \InvalidArgumentException('Items of list must be other lists');
            }
            $all = array_merge($all, $sub_array);
        }

        return $all;
    }

    /**
     * @param \Traversable|array $array
     *
     * @return array
     */
    public static function flatten($array)
    {
        $ret = [];
        foreach ($array as $a) {
            if (is_array($a) || $a instanceof \Traversable) {
                $ret = array_merge($ret, self::flatten($a));
            } else {
                $ret[] = $a;
            }
        }

        return $ret;
    }

    /**
     * Remove duplicates form the list. This is a more powerful version of array_unique.
     *
     * $cmp can be:
     * - === to do a strict check
     * - == to do a non-strict check
     * - callable($a, $b) to do a custom comparison. Return a truthy value when they are equal.
     *
     * @param \Traversable|array $array
     * @param string|callable    $cmp
     *
     * @return array
     */
    public static function unique($array, $cmp = '===')
    {
        $ret = [];

        foreach ($array as $v) {
            if (empty($ret)) {
                $ret[] = $v;
            } else {
                if ($cmp === '===') {
                    if (!in_array($v, $ret, true)) {
                        $ret[] = $v;
                    }
                } elseif ($cmp === '==') {
                    if (!in_array($v, $ret)) {
                        $ret[] = $v;
                    }
                } else {
                    $found = false;
                    foreach ($ret as $existV) {
                        if (call_user_func($cmp, $v, $existV)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $ret[] = $v;
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Sorts a list using the return value of $fn(item).
     *
     * The return value of an item is typically an integer. If it is not,
     * we will try to handle it th ebest we can (e.g. true/false, nulls, objects with toString, etc).
     *
     * @param \Traversable|array $array
     * @param callable           $fn
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

        $array2 = $array;
        usort($array2, function ($a, $b) use ($fn, $procType, $reverse) {
            $aVal = $procType($fn($a));
            $bVal = $procType($fn($b));

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

        return $array2;
    }

    /**
     * @param \Traversable|array $array
     * @param callable|null      $fn    Optionally a function to filter the results by
     *
     * @return mixed
     */
    public static function first($array, $fn = null)
    {
        if ($fn) {
            foreach ($array as $v) {
                if (call_user_func($fn, $v)) {
                    return $v;
                }
            }

            return;
        } else {
            if (!is_array($array) && !$array instanceof \ArrayAccess) {
                $array = iterator_to_array($array, false);
            }

            if (empty($array)) {
                throw new \InvalidArgumentException('Array is empty');
            }

            // This casts a map to a list
            if (!array_key_exists(0, $array)) {
                $array = array_values($array);
            }

            return $array[0];
        }
    }

    /**
     * Get the last element of a list.
     *
     * @param \Traversable|array $array
     * @param callable|null      $fn    Optionally a function to filter the results by
     *
     * @return mixed
     */
    public static function last($array, $fn = null)
    {
        if ($fn) {
            foreach (new ReverseIterator($array) as $v) {
                if (call_user_func($fn, $v)) {
                    return $v;
                }
            }

            return;
        } else {
            if (!is_array($array) && !($array instanceof \ArrayAccess && $array instanceof Countable)) {
                $array = iterator_to_array($array, false);
            }

            if (empty($array)) {
                throw new \InvalidArgumentException('Array is empty');
            }

            // This casts a map to a list
            if (!array_key_exists(0, $array)) {
                $array = array_values($array);
            }

            $len = count($array);

            return $array[$len - 1];
        }
    }

    /**
     * Checks if two arrays contain the same elements in any order.
     *
     * @param \Traversable|array $array1
     * @param \Traversable|array $array2
     *
     * @return bool
     */
    public static function isSame($array1, $array2)
    {
        if (count($array1) !== count($array2)) {
            return false;
        }

        $diff1 = array_diff($array1, $array2);
        if (!empty($diff1)) {
            return false;
        }

        $diff2 = array_diff($array2, $array1);
        if (!empty($diff2)) {
            return false;
        }

        return true;
    }
}
