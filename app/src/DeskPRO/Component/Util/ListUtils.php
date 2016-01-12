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
     * Remove all falsey values from an array.
     *
     * @param array $array The array to work on
     *
     * @return array
     */
    public static function filterOutFalsey($array)
    {
        $new = array();

        foreach ($array as $v) {
            if ($v) {
                $new[] = $v;
            }
        }

        return $new;
    }

    /**
     * @param array       $array
     * @param array|mixed $values Values to remove
     * @param bool        $strict Strict checking on $values
     *
     * @return array
     */
    public static function filterOutValues($array, $values, $strict = true)
    {
        $new = array();

        if (!is_array($values) && !($array instanceof \Traversable)) {
            $values = array($values);
        }

        foreach ($array as $v) {
            if (!in_array($v, $values, $strict)) {
                $new[] = $v;
            }
        }

        return $new;
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
}
