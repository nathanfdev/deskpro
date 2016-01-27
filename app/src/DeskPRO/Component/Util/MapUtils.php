<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
        $new = array();

        foreach ($array as $k => $v) {
            if ($v) {
                $new[$k] = $v;
            }
        }

        return $new;
    }

    /**
     * @param $array
     * @param array|mixed $values Values to remove
     * @param bool        $strict Strict checking on $values
     *
     * @return array
     */
    public static function filterOutValues($array, $values, $strict = true)
    {
        $new = array();

        if (!is_array($values)) {
            $values = array($values);
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
     * @param $key
     * @param $val
     *
     * @return array
     */
    public static function prependItem($array, $key, $val)
    {
        $new = array($key => $val);

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
        $new = array();

        foreach ($array as $k => $v) {
            $set_k = call_user_func($fn, $v, $k);
            if ($set_k !== null) {
                $new[$set_k] = $v;
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
        $new = array();

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
        $new = array();

        foreach ($array as $v) {
            if (isset($v->$key)) {
                $new[$v->$key] = $v;
            }
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

            $return[$user_return[0]] = $user_return[1];
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
}
