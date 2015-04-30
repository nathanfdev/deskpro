<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

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
    private function __construct() {}

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
     * @param array  $values Values to remove
     * @param bool   $strict  Strict checking on $values
     * @return array
     */
    public static function filterOutValues($array, array $values, $strict = true)
    {
        $new = array();

        foreach ($array as $k => $v) {
            if (in_array($v, $values, $strict)) {
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
     * @return array
     */
    public static function prependItem($array, $key, $val)
    {
        $new = array($key => $val);

        foreach ($array as $k => $v) {
            $new[$k] = $v;
        }

        return $new;
    }


    /**
     * Create a new array and key it by using a callback on each item of the input array.
     *
     * @param \Traversable|array $array
     * @param callable $fn
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
     * @param string $key
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
     * @param string $key
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
}