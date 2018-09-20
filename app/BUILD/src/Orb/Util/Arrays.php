<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Utility functions that work with arrays.
 *
 * @static
 */
class Arrays
{
    private function __construct()
    { /* No instances allowed */
    }

    /**
     * Used as the value placeholder when defining parameters to pass to a user
     * function with Outershift_Array::func().
     *
     * @var string
     *
     * @see Arrays::func()
     */
    const FUNC_ARR_VAL = '___ORB_ARR_VALUE___';

    /**
     * Used as the value to indicate that values that aren't set should be ignored and not
     * included in the resulting reduced array.
     *
     * @var string
     *
     * @see Arrays::reduceToKeys()
     */
    const REDUCE_IGNORE_UNSET = '___ORB_IGNORE_UNSET___';

    /**
     * Used to indicate that an array item is not set.
     */
    const ARR_KEY_NOT_SET = '___ORB_ARR_KEY_NOT_SET___';

    /**
     * Represents that duplicate lowercase keys are overwritten. The value
     * that appears later in the array is kept.
     *
     * @var int
     *
     * @see Arrays::lowercaseKeys()
     */
    const LOWERKEY_DUPE_OVERWRITE = 1;

    /**
     * Represents that duplicate lowercase keys are kept in a sub-array.
     *
     * @var int
     *
     * @see Arrays::lowercaseKeys()
     */
    const LOWERKEY_DUPE_ADD_ARRAY = 2;

    /**
     * Flattens a multidimentional array into a single dimentional array.
     * This MAY result in data loss if there are duplicate keys in associative arrays.
     *
     * @param array $array The array to flatten
     *
     * @return array The flattened array
     */
    public static function flatten($array)
    {
        if (!is_array($array)) {
            return (array) $array;
        }

        $new_array = [];

        foreach ($array as $k => $v) {
            if (!is_array($v)) {
                if (is_int($k)) {
                    $new_array[] = $v;
                } else {
                    $new_array[$k] = $v;
                }
            } else {
                $v         = self::flatten($v);
                $new_array = array_merge($new_array, $v);
            }
        }

        return $new_array;
    }

    /**
     * Flattens a multidimentional array into a single dimentional array, and separates
     * sub-array keys with $sep.
     *
     * <code>
     * $array = array('christopher' => array('id' => 22, 'group' => 'admin'));
     * $array = Arrays::flattenWithKeys($array);
     * // -> christopher.id=22
     * //    christopher.group=admin
     * </code>
     *
     * @param array  $array The array to work on
     * @param string $sep   The separate to use between key names
     *
     * @return array
     */
    public static function flattenWithKeys($array, $sep = '.')
    {
        return self::_flattenWithKeys($array, $sep);
    }

    protected static function _flattenWithKeys($array, $sep = '.', array $key_parts = [])
    {
        $new_array = [];

        if ($key_parts) {
            $key_prefix = implode($sep, $key_parts).$sep;
        } else {
            $key_prefix = '';
        }

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $key_parts[] = $k;
                $v           = self::_flattenWithKeys($v, $sep, $key_parts);
                $new_array   = array_merge($new_array, $v);
                array_pop($key_parts);
            } else {
                $k             = $key_prefix.$k;
                $new_array[$k] = $v;
            }
        }

        return $new_array;
    }

    /**
     * Gets the value of the array at $key if it exists. If the value is an array,
     * it just returns the first value of that array.
     *
     * @param array $array
     * @param       $key
     * @param null  $default
     *
     * @return mixed|null
     */
    public static function reachForFirstValueInKey(array $array, $key, $default = null)
    {
        if (!array_key_exists($key, $array)) {
            return $default;
        }

        $val = $array[$key];

        if (is_array($val) && count($val) > 0) {
            $values = array_values($val);

            return array_shift($values);
        }

        return $val ?: $default;
    }

    /**
     * Run a function on all items of an array recursively. This is a more powerful version of
     * array_walk().
     *
     * Note: You can change the placement of the array value when calling the functions by using
     * the value Arrays::FUNC_ARR_VAL in the $params array. If it does not exist, it will
     * be the first parameter.
     *
     * Example:
     * <code>
     * // Strip magic quotes
     * $_GET = Arrays::func($_GET, 'stripslashes');
     *
     * // Example of custom parameters
     * $var = Arrays::func($array, 'somefunc', array(1, 2, Arrays::FUNC_ARR_VAL));
     * // Calls somefunc(1, 2, $array[index]) for each index.
     * </code>
     *
     * @param mixed  $array       The array or value to run $func on
     * @param string $func        The function to run
     * @param array  $params      Parameters to pass to $func
     * @param bool   $run_on_keys Also run the function on the keys (useful for ex stripslashes)
     *
     * @return mixed The value (usually array) returned by $func on all items
     */
    public static function func($array, $func, $params = [], $run_on_keys = false)
    {
        if (!is_array($array)) {
            return self::_func_run_func($func, $params, $array);
        }

        foreach ($array as $k => $v) {
            if ($run_on_keys) {
                $k = self::_func_run_func($func, $params, $k);
            }
            $array[$k] = self::func($v, $func, $params);
        }

        return $array;
    }

    protected static function _func_run_func($func, $params, $val)
    {
        $key = array_search(self::FUNC_ARR_VAL, $params, true);

        if ($key === false) {
            if (array_key_exists(0, $params)) {
                array_unshift($params, '');
            }

            $key = 0;
        }

        $params[$key] = $val;

        return call_user_func_array($func, $params);
    }

    /**
     * Merge multiple associative arrays together.
     *
     * This differs from PHP's array_merge() in that numeric keys are not discarded.
     *
     * <code>
     * $arr1 = array(0 => 'None', 5 => 'User5', 22 => 'User22');
     * $arr2 = array(14 => 'User14', 2 => 'User2');
     *
     * array_merge($arr1, $arr2)
     *     -> array('None', 'User5', 'User22', 'User14', 'User2')
     *        Indexed 0 to 5, the userid keys are lost.
     *
     * Arrays::mergeAssoc($arr1, $arr2)
     *     -> array(0 => 'None', 5 => 'User5', 22 => 'User22', 14 => 'User14', 2 => 'User2')
     *        Userid keys remain unchanged
     * </code>
     *
     * @param array $array      The initial array
     * @param array $another... An array to merge into the original
     *
     * @return array
     */
    public static function mergeAssoc()
    {
        $new_array = (array) func_get_arg(0);

        for ($i = 1, $size = func_num_args(); $i < $size; ++$i) {
            $arr = (array) func_get_arg($i);

            foreach ($arr as $key => $val) {
                $new_array[$key] = $val;
            }
        }

        return $new_array;
    }

    /**
     * Merge two or more arrays together recursively.
     *
     * @param array $array...
     *
     * @return array
     */
    public static function mergeDeep()
    {
        $args = func_get_args();

        if (!$args) {
            return [];
        }
        if (sizeof($args) == 1) {
            return $args[0];
        }

        $array = array_shift($args);

        while (($other_array = array_shift($args)) !== null) {
            $array = self::_mergeDeepHelper($array, $other_array);
        }

        return $array;
    }

    /**
     * Recursively merges two arrays together.
     *
     * @param array      $array1
     * @param array|null $array2
     *
     * @return array
     */
    protected static function _mergeDeepHelper(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                                  ? self::_mergeDeepHelper($array1[$key], $array2[$key])
                                  : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }

        return $array1;
    }

    /**
     * Goes through an array and makes sure each sub-array contains only unique items.
     *
     * @param array $array
     *
     * @return array
     */
    public static function uniqueDeep(array $array, $sort_flags = SORT_STRING)
    {
        $args = func_get_args();

        if (!$args) {
            return [];
        }
        if (sizeof($args) == 1) {
            return $args[0];
        }

        return self::_uniqueDeep($array, $sort_flags);
    }

    protected static function _uniqueDeep(array $array, $sort_flags)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = array_unique(self::_uniqueDeep($v, $sort_flags), $sort_flags);
            } else {
                $array[$k] = $v;
            }
        }

        return array_unique($array);
    }

    /**
     * Like array_unique but works with object identity.
     *
     * @param array $objects
     *
     * @return array
     */
    public static function uniqueObjectArray(array $objects)
    {
        $map = [];
        $res = [];

        foreach ($objects as $k => $o) {
            $oid = spl_object_hash($o);
            if (!isset($map[$oid])) {
                $map[$oid] = true;
                $res[]     = $o;
            }
        }

        return $res;
    }

    /**
     * Add a new value to the beginning of an associative array.
     *
     * Like PHP's array_unshift(), but works on associative arrays.
     *
     * <code>
     * $arr = array(5 => 'User5', 22 => 'User22');
     *
     * array_unshift($arr, 'None')
     *     -> array('None', 'User5', 'User22')
     *        Indexed 0 to 2, the userid keys are lost.
     *
     * Arrays::unshiftAssoc($arr, 0, 'None')
     *     -> array(0 => 'None', 5 => 'User5', 22 => 'User22')
     *        Userid keys remain unchanged
     * </code>
     *
     * @param array $array The array to work on
     * @param mixed $key   The key of the item to add
     * @param mixed $value The value of the item to add
     *
     * @return int Size of new array
     */
    public static function unshiftAssoc(&$array, $key, $value = false)
    {
        if (!is_array($array)) {
            $array = (array) $array;
        }

        $old_array = $array;
        $array     = [$key => $value];

        // Make sure not to overwrite it with old value
        if (isset($old_array[$key])) {
            unset($old_array[$key]);
        }

        foreach ($old_array as $k => $v) {
            $array[$k] = $v;
        }

        return sizeof($array);
    }

    /**
     * Just like unshiftAssoc() except this creates a copy of the array
     * and returns it.
     *
     * @return array
     */
    public static function unshiftAssocReturn($array, $key, $value)
    {
        self::unshiftAssoc($array, $key, $value);

        return $array;
    }

    /**
     * Just like array_diff except works with === identity checks (good for checking objects).
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function arrayDiffIdentity(array $array1, array $array2)
    {
        return array_udiff(
            $array1,
            $array2,
            function ($a, $b) {
                return $a === $b ? 0 : -1;
            }
        );
    }

    /**
     * Just like array_diff_assoc except works with === identity checks (good for checking objects).
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function arrayDiffAssocIdentity(array $array1, array $array2)
    {
        return array_udiff_assoc(
            $array1,
            $array2,
            function ($a, $b) {
                return $a === $b ? 0 : -1;
            }
        );
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function arrayDiffAssocRecursive(array $array1, array $array2)
    {
        $diff = [];

        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists($key, $array2) || !is_array($array2[$key])) {
                    $diff[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffAssocRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $diff[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }

    /**
     * Remove all falsey values from an array.
     *
     * @param array $array The array to work on
     *
     * @return mixed
     */
    public static function removeFalsey($array)
    {
        if (!is_array($array)) {
            $array = (array) $array;
        }

        foreach (array_keys($array) as $k) {
            if (!$array[$k]) {
                unset($array[$k]);
            }
        }

        return $array;
    }

    /**
     * Removes all items in $array except for $keys.
     *
     * @param array           $array     The array to work on
     * @param string|string[] $keys      A key or array of keys to keep
     * @param bool            $recursive To traverse down the array
     *
     * @return array
     */
    public static function removeButKey(array $array, $keys, $recursive = false, $ignore_numeric = false)
    {
        $new = [];

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $keys = array_combine($keys, $keys);

        foreach ($array as $k => $v) {
            if (isset($keys[$k]) || ($ignore_numeric && is_numeric($k))) {
                if ($recursive && is_array($v)) {
                    $v = self::removeButKey($v, $keys, true, $ignore_numeric);
                    if ($v) {
                        $new[$k] = $v;
                    }
                } else {
                    $new[$k] = $v;
                }
            }
        }

        return $new;
    }

    /**
     * Recurse into an array and rename keys.
     *
     * @param array  $array     The array to work on
     * @param string $old_key   The old key
     * @param string $new_key   The new key
     * @param int    $max_depth How deep down the array to recurse. -1 for unlimited depth
     *
     * @return array
     */
    public static function multiRenameKey(array $array, $old_key, $new_key, $max_depth = -1, $_cur_depth = 0)
    {
        $new = [];

        foreach ($array as $k => $v) {
            if ($k == $old_key) {
                $k = $new_key;
            }

            if (is_array($v) && $max_depth == -1 || $_cur_depth < $max_depth) {
                $v = self::multiRenameKey($v, $old_key, $new_key, $max_depth, $_cur_depth + 1);
            }

            $new[$k] = $v;
        }

        return $new;
    }

    /**
     * @param array  $array
     * @param string $recursive_key A string to recurse down only speciifc keys (eg, only reindex 'children')
     *
     * @return array
     */
    public static function assocToNumericArray(array $array, $recursive_key = null)
    {
        $new = [];

        foreach ($array as $v) {
            if ($recursive_key && isset($v[$recursive_key]) && is_array($v[$recursive_key])) {
                $v[$recursive_key] = self::assocToNumericArray($v[$recursive_key], $recursive_key);
            }

            $new[] = $v;
        }

        return $new;
    }

    /**
     * Takes a lookup array and
     * reverses it so the value is the new key, and the key is the new value.
     *
     * E.g.:
     * <code>
     * $map = array(1 => array('x', 'y', 'z'), 2 => array('x'));
     * $rmap = reverseLookupArray($map, true);
     * // $rmap = array('x' => array(1, 2), 'y' => array(1), 'z' => array(1));
     * </code>
     *
     * @param array $array
     * @param bool  $map_to_array True if the inner array (the thing being mapped to) should itself be an array
     * @param bool  $with_key     When $map_to_array is used, $with_key will key the innser arary with the value itself (so you can do isset() checks on it)
     *
     * @return array
     */
    public static function reverseLookupArray($array, $map_to_array = false, $with_key = false)
    {
        $new = [];

        foreach ($array as $outer_id => $inner) {
            if (is_array($inner) || $inner instanceof \Traversable) {
                foreach ($inner as $inner_id) {
                    if ($map_to_array) {
                        if (!isset($new[$inner_id])) {
                            $new[$inner_id] = [];
                        }

                        if ($with_key) {
                            $new[$inner_id][$outer_id] = $outer_id;
                        } else {
                            $new[$inner_id][] = $outer_id;
                        }
                    } else {
                        $new[$inner_id] = $outer_id;
                    }
                }
            } else {
                if ($map_to_array) {
                    if (!isset($new[$inner])) {
                        $new[$inner] = [];
                    }

                    if ($with_key) {
                        $new[$inner][$outer_id] = $outer_id;
                    } else {
                        $new[$inner][] = $outer_id;
                    }
                } else {
                    $new[$inner] = $outer_id;
                }
            }
        }

        return $new;
    }

    /**
     * Remove all values from an array that are empty strings. This differs
     * from removeFalsey() in that only empty strings are removed, things like
     * null or 0 are preserved. Note that strings are trim()'ed before being
     * tested for emptiness.
     *
     * @param array $array The array to search in
     *
     * @return array
     */
    public static function removeEmptyString($array)
    {
        if (!is_array($array)) {
            $array = (array) $array;
        }

        foreach (array_keys($array) as $k) {
            if (is_string($array[$k]) and trim($array[$k]) === '') {
                unset($array[$k]);
            }
        }

        return $array;
    }

    /**
     * Remove all values from an array that are an empty array. This differs
     * from removeFalsey() in that only empty arrays are removed.
     *
     * @param array $array The array to search in
     *
     * @return array
     */
    public static function removeEmptyArray($array)
    {
        if (!is_array($array)) {
            $array = (array) $array;
        }

        foreach (array_keys($array) as $k) {
            if (is_array($array[$k]) and !$array[$k]) {
                unset($array[$k]);
            }
        }

        return $array;
    }

    /**
     * Remove all values from an array that are an empty array. This differs
     * from removeFalsey() in that only empty arrays are removed.
     *
     * @param array $array The array to search in
     *
     * @return array
     */
    public static function removeNull($array)
    {
        if (!is_array($array)) {
            $array = (array) $array;
        }

        foreach (array_keys($array) as $k) {
            if ($array[$k] === null) {
                unset($array[$k]);
            }
        }

        return $array;
    }

    /**
     * Take an array of arrays and then use data from the sub-arrays as keys in the
     * main array. Example:.
     *
     * <code>
     * $arr1 = array( array('id' => 1, 'value' => 'foo'), array('id' => 2, 'value' => 'bar') )
     * $arr2 = Arrays::keyFromData($arr1, 'id', 'value');
     * // Is now: array(1 => 'foo', 2 => 'bar')
     * </code>
     *
     * If $val_index is supplied, the array is 'flattened' and only the value from the index
     * is added.
     *
     * @param array $array     The array to work with
     * @param mixed $key_index The index of the sub-arrays to use as the key
     * @param bool  $val_index The index of the only data item to return
     */
    public static function keyFromData($array, $key_index = 0, $val_index = false)
    {
        $new_array = [];

        if (!$val_index) {
            foreach ($array as $sub_array) {
                $new_array[$sub_array[$key_index]] = $sub_array;
            }
        } else {
            foreach ($array as $sub_array) {
                $new_array[$sub_array[$key_index]] = $sub_array[$val_index];
            }
        }

        return $new_array;
    }

    /**
     * Rekey an array using a callback function on each value.
     * Callback can return null and the item will not be included in the array.
     *
     * @param array    $array
     * @param callback $fn    Callback should take two params: $v, $k. Should return a new key or null to skip
     *
     * @return array
     */
    public static function rekey($array, $fn)
    {
        $new_array = [];

        foreach ($array as $k => $v) {
            $new_k = call_user_func($fn, $v, $k);
            if ($new_k !== null) {
                $new_array[$new_k] = $v;
            }
        }

        return $new_array;
    }

    /**
     * Push values into the array as long as they are not already in the array.
     *
     * Note this does a weak comparison (== instead of ===).
     *
     * @param array $array The array to work on
     * @param mixed $val   ...  The values to push
     *
     * @return int The new number of elements in the array
     */
    public static function pushUnique(&$array, $val)
    {
        $num = func_num_args();

        for ($i = 1; $i < $num; ++$i) {
            $val = func_get_arg($i);

            if (!in_array($val, $array)) {
                array_push($array, $val);
            }
        }

        return sizeof($array);
    }

    /**
     * Same as pushUnique() except this does strict comparisons (=== instead of ==).
     *
     * @see      Arrays::pushUnique()
     *
     * @param array $array The array to work on
     * @param mixed $val   ...  The values to push
     *
     * @return int The new number of elements in the array
     */
    public static function pushUniqueStrict(&$array, $val)
    {
        $num = func_num_args();

        for ($i = 1; $i < $num; ++$i) {
            $val = func_get_arg($i);

            if (!in_array($val, $array, true)) {
                array_push($array, $val);
            }
        }

        return sizeof($array);
    }

    /**
     * Get the value from a multidimentional array using a path-like syntax.
     *
     * <code>
     * $array = array('user1' => array('groups' => array(1 => array('name' => 'Admin'))));
     * $group_name = Arrays::keyAsPath('/user1/groups/1/name/', $array);
     * echo $group_name; // Admin
     * </code>
     *
     * If the path could not be resolved, null is returned as the value. Note that null may be
     * a legitimate value. That is, if the value in the array is actually null then you have no
     * way to know if it was found or not.
     *
     * @param array  $array    The array to work with
     * @param string $path     The path
     * @param string $path_sep The string to use as the path separator
     * @param mixed  $default  Default value if the key doesnt eixst
     *
     * @return mixed The value at the end of the path
     */
    public static function keyAsPath($array, $path, $path_sep = '/', $default = null)
    {
        if (!$path_sep) {
            return;
        }

        // If its not a path at all, we can do a simple lookup
        if (strpos($path, $path_sep) === false) {
            return isset($array[$path]) ? $array[$path] : $default;
        }

        // Remove leading+trailing seps
        if (Strings::startsWith($path_sep, $path)) {
            $path = substr($path, 1);
        }

        if (Strings::endsWith($path_sep, $path)) {
            $path = substr($path, 0, strlen($path) - 1);
        }

        $parts = explode($path_sep, $path);

        if (!$parts) {
            return $default;
        }

        while (($key = array_shift($parts)) !== null) {
            if (!isset($array[$key])) {
                return $default;
            }

            $array = $array[$key];
        }

        return $array;
    }

    /**
     * Get a deep value from a multidimentional array using a dot to separate keys.
     *
     * <code>
     * $array = array('chroder' => array('info' => array('name' => 'Christopher')));
     * $name = Arrays::getValue('chroder.info.name');
     * </code>
     *
     * Note: This is really just an alias for keyAsPath() with $path_sep to '.'
     *
     * @param array  $array The array to work with
     * @param string $key   The dotted key
     *
     * @return mixed
     */
    public static function getValue($array, $key, $default = null)
    {
        return self::keyAsPath($array, $key, '.', $default);
    }

    /**
     * Takes a nested array and returns a flat version where sub-keys are separated with a dot.
     *
     * @param array  $array
     * @param string $sub_sep
     * @param string $_start_key {internal}
     * @param array  $_result    {internal}
     *
     * @return array
     */
    public static function flattenKeyValueArray($array, $sub_sep = '.', $_start_key = '', array &$_result = null)
    {
        if (!$_result) {
            $_result = [];
        }

        foreach ($array as $k => $v) {
            $real_k = $_start_key ? $_start_key.$sub_sep.$k : $k;

            if (is_array($v) || $v instanceof \Traversable) {
                self::flattenKeyValueArray($v, $sub_sep, $real_k, $_result);
            } else {
                $_result[$real_k] = $v;
            }
        }

        return $_result;
    }

    /**
     * Returns a string from an array using the given template on each item. Sortof like
     * implode() but a bit more control.
     *
     * @param array  $array The array to work with
     * @param string $tpl   The template to use. Variables {VAL} and {KEY} are available
     *
     * @return string
     */
    public static function implodeTemplate($array, $tpl = '<li>{VAL}</li>', $key_prefix = null)
    {
        $string = '';

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $string .= self::implodeTemplate($v, $tpl, "$k.");
            } else {
                $key_str = ($key_prefix ?: '').$k;
                $string .= str_replace(['{KEY}', '{VAL}'], [$key_str, $v], $tpl);
            }
        }

        return $string;
    }

    /**
     * Like var_export except the result is prettier.
     *
     * @param array $array
     *
     * @return string
     */
    public static function prettyDump(array $array, $_level = 0)
    {
        if (!count($array)) {
            return 'array()';
        }

        if (isset($array[0])) {
            $is_numeric_array = true;
            $max_keylen       = 0;
        } else {
            $is_numeric_array = false;
            $max_keylen       = 0;

            foreach ($array as $k => $v) {
                $len = strlen($k);
                if ($len > $max_keylen) {
                    $max_keylen = $len;
                }
            }

            $max_keylen += 3;
        }

        $rows = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $v = self::prettyDump($v, $_level + 1);
                $v = ltrim($v);

                if (strlen($v) < 80) {
                    $v = str_replace("\n", ' ', $v);
                    $v = preg_replace('#\s*=>\s*#', ' => ', $v);
                    $v = preg_replace("#',\s*'#", '\', \'', $v);
                    $v = trim($v);
                    $v = preg_replace('#^array\(\s*#', 'array(', $v);
                    $v = preg_replace('#\s*\)$#', ')', $v);
                    $v = preg_replace('#,\)$#', ')', $v);
                }
            } else {
                $v = var_export($v, true);
            }

            if ($is_numeric_array) {
                $row = str_repeat("\t", $_level + 1).$v;
            } else {
                $row = sprintf("%s%-{$max_keylen}s => %s", str_repeat("\t", $_level + 1), var_export($k, true), $v);
            }

            $rows[] = $row;
        }

        $rows = implode(",\n", $rows);
        $rows .= ',';

        $output = str_repeat("\t", $_level)."array(\n".$rows."\n".str_repeat("\t", $_level).')';

        return $output;
    }

    /**
     * Take an array of id=>array(data) items and create a hierarchy based on a parent_id element
     * in the data array.
     *
     * <code>
     * $data = array(
     *     1 => array('parent_id' => 0, 'title' => 'Title 1'),
     *     2 => array('parent_id' => 1, 'title' => 'Title 2')
     * );
     * $data2 = Arrays::intoHierarchy($data, 'parent_id', 'children');
     * // $data2 = array(
     * //            1 => array(
     * //                'parent_id' => 0,
     * //                'title' => 'Title 1',
     * //                'children' => array(2 => array('parent_id' => 1, 'title' => 'Title 2'))
     * //            )
     * // );
     * </code>
     *
     * @param array  $array      The array to work on
     * @param array  $top_id     The top of the hierarchy (i.e., level 0, or 'no parent', 'top', etc)
     * @param string $parent_key The key in the data to use as the parent_id
     * @param string $child_key  The key to add that contains the children
     * @param string $store_ids  A variable to put all the keys that make it into the array
     *
     * @return array
     */
    public static function intoHierarchy(
        $array,
        $top_id = 0,
        $parent_key = 'parent_id',
        $child_key = 'children',
        &$store_ids = null
    ) {
        $store_ids = [];

        return self::_intoHierarchy($array, $top_id, $parent_key, $child_key, $store_ids);
    }

    // Helper function takes the array by ref to save time/memory by unsetting each processed
    // element as it goes.
    protected static function _intoHierarchy(&$array, $top_id, $parent_key, $child_key, &$store_ids = null)
    {
        $new_array = [];

        foreach (array_keys($array) as $id) {
            if (!isset($array[$id]) or $array[$id][$parent_key] != $top_id) {
                continue;
            }

            $store_ids[] = $id;

            $new_array[$id] = $array[$id];

            unset($array[$id]);
            $new_array[$id][$child_key] = self::intoHierarchy($array, $id, $parent_key, $child_key, $store_ids);
        }

        return $new_array;
    }

    /**
     * Flattens a hierarchical array into a "flat" hierarchy. This is where all items exist in a single-dimentional
     * array, but are in order with a new 'depth' field to indicate "indentation". This is useful when you have
     * an array formatted like Arrays::intoHierarchy().
     *
     * <code>
     * $data = array(
     *             1 => array(
     *                 'parent_id' => 0,
     *                 'title' => 'Title 1',
     *                 'children' => array(2 => array('parent_id' => 1, 'title' => 'Title 2'))
     *             )
     * );
     *
     * // to
     * $data2 = array(
     *     1 => array('parent_id' => 0, 'title' => 'Title 1', 'depth' => 0),
     *     2 => array('parent_id' => 1, 'title' => 'Title 2', 'depth' => 1)
     * );
     * </code>
     *
     * @param array  $array     The array of data to work on
     * @param string $index_key The key to use when putting items into the data array, null for no key (which results in normal integer arrays)
     * @param string $child_key Which item contains the "children" in each item in the array?
     * @param string $depth_key The key to use to put the integer 'depth' that represents an items level
     *
     * @return array
     */
    public static function flattenHierarchy(
        array $array,
        $index_key = 'id',
        $child_key = 'children',
        $depth_key = 'depth'
    ) {
        $new_array = [];

        self::_flattenHierarcy($new_array, $array, $index_key, $child_key, $depth_key, 0);

        return $new_array;
    }

    protected static function _flattenHierarcy(
        array &$new_array,
        $array,
        $index_key,
        $child_key,
        $depth_key,
        $current_depth = 0,
        &$count = 0
    ) {
        foreach ($array as $arr) {
            if ($index_key !== null) {
                $index = $arr[$index_key];
            } else {
                $index = $count;
            }

            ++$count;

            $new_array[$index]          = $arr;
            $new_array[$index]['depth'] = $current_depth;

            if (isset($arr[$child_key]) and $arr[$child_key]) {
                $sub_array = $arr[$child_key];
                if (!is_array($sub_array)) {
                    $sub_array = iterator_to_array($sub_array);
                }
                self::_flattenHierarcy(
                    $new_array,
                    $sub_array,
                    $index_key,
                    $child_key,
                    $depth_key,
                    $current_depth + 1,
                    $count
                )
                ;
            }
        }
    }

    /**
     * @param $array
     * @param string $order_key
     * @param string $parent_key
     * @param bool   $keep_keys
     */
    public static function sortFlatHierarchyArray(
        &$array,
        $order_key = 'display_order',
        $parent_key = 'parent',
        $keep_keys = false
    ) {
        $sort_fn = $keep_keys ? 'uasort' : 'usort';

        $sort_fn(
            $array,
            function ($a, $b) use ($order_key, $parent_key) {
                if ($a[$parent_key]) {
                    $a_order = floatval($a[$parent_key][$order_key].'.'.$a[$order_key]);
                } else {
                    $a_order = floatval($a[$order_key]);
                }

                if ($b[$parent_key]) {
                    $b_order = floatval($b[$parent_key][$order_key].'.'.$b[$order_key]);
                } else {
                    $b_order = floatval($b[$order_key]);
                }

                if ($a_order == $b_order) {
                    return 0;
                }

                return $a_order < $b_order ? -1 : 1;
            }
        );
    }

    /**
     * Takes an array hierarchy and converts it into a k=>title array suitable for a flat select box.
     *
     * @param array  $array
     * @param string $index_key
     * @param string $title_key
     * @param string $indent
     *
     * @return array
     */
    public static function selectArrayFromHierarchy($array, $index_key = 'id', $title_key = 'title', $indent = '--')
    {
        if (!is_array($array)) {
            $array = iterator_to_array($array);
        }
        $flat = self::flattenHierarchy($array);

        $options = [];
        foreach ($flat as $i) {
            $indent = '';
            if (!empty($i['depth']) and $i['depth'] > 0) {
                $indent = str_repeat($indent, $i['depth']).' ';
            }
            $options[$i[$index_key]] = $indent.$i[$title_key];
        }

        return $options;
    }

    /**
     * Reduce an array to only specified keys.
     *
     * Set $default to Arrays::REDUCE_IGNORE_UNSET if you do not want to
     * include keys that don't exist in the original array.
     *
     * @param array $array   The original array
     * @param array $keys    What keys to preserve
     * @param mixed $default The default value to set, if the original array doesn't have a key
     *
     * @return array
     */
    public static function reduceToKeys(array $array, array $keys, $default = self::REDUCE_IGNORE_UNSET)
    {
        $ret = [];

        foreach ($keys as $k) {
            if (isset($array[$k])) {
                $ret[$k] = $array[$k];
            } elseif ($default != self::REDUCE_IGNORE_UNSET) {
                $ret[$k] = $default;
            }
        }

        return $ret;
    }

    /**
     * Run reduceToKeys() on an array of arrays. Useful on collections for example.
     *
     * @param array $mutli_array The original array
     * @param array $keys        What keys to preserve
     * @param mixed $default     The default value to set, if the orig array doesn't have a key
     *
     * @return array
     */
    public static function reduceToKeysMulti(array $mutli_array, array $keys, $default = self::REDUCE_IGNORE_UNSET)
    {
        $ret = [];
        foreach ($mutli_array as $k => $v) {
            $ret[$k] = self::reduceToKeys($v, $keys, $default);
        }

        return $ret;
    }

    /**
     * Take a multidimential array and return a new array where
     * only a single index exists.
     *
     * <code>
     * $array = array(
     *     4 => array('userid' => 4, 'name' => 'Christopher'),
     *     15 => array('userid' => 15, 'name' => 'Danny')
     * );
     *
     * $new = Arrays::flattenToIndex($array, 'name');
     *
     * // $new is now:
     * // array(4 => 'Christopher', 15 => 'Danny')
     * </code>
     *
     * @param array      $array The array to work on
     * @param string|int $index The index of the immediate sub-array to use
     *
     * @return array
     */
    public static function flattenToIndex($array, $index = 0, $ignore_keys = false)
    {
        $ret = [];

        if ($ignore_keys) {
            foreach ($array as $sub_array) {
                if (isset($sub_array[$index])) {
                    $ret[] = $sub_array[$index];
                }
            }
        } else {
            foreach ($array as $k => $sub_array) {
                if (isset($sub_array[$index])) {
                    $ret[$k] = $sub_array[$index];
                }
            }
        }

        return $ret;
    }

    /**
     * Cast array values and/or keys to a specific type. Pass
     * null to $values or $keys to skip casting of that thing.
     *
     * @param array  $array    The array to work on
     * @param string $val_type The type to cast values to
     * @param string $key_type The type to cast keys to
     *
     * @return array
     */
    public static function castToType(array $array, $val_type = 'string', $key_type = null)
    {
        $ret = [];

        foreach ($array as $k => $v) {
            if ($key_type !== null && $key_type != 'discard') {
                settype($k, $key_type);
            }

            if ($val_type !== null) {
                settype($v, $val_type);
            }

            if ($key_type === 'discard') {
                $ret[] = $v;
            } else {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    /**
     * Same as castToType except recursively goes into subarrays.
     *
     * @param array  $array    The array to work on
     * @param string $val_type The type to cast values to
     * @param string $key_type The type to cast keys to
     *
     * @return array
     */
    public static function castToTypeDeep(array $array, $val_type = 'string', $key_type = null)
    {
        $ret = [];

        foreach ($array as $k => $v) {
            if ($key_type !== null) {
                settype($k, $key_type);
            }

            if ($val_type !== null) {
                if (is_array($v)) {
                    $v = self::castToTypeDeep($v, $val_type, $key_type);
                } else {
                    settype($v, $val_type);
                }
            }

            $ret[$k] = $v;
        }

        return $ret;
    }

    /**
     * Get the Nth key in the array. Obviously only useful for
     * non-numerical indexed arrays.
     *
     * <code>
     * $arr = array('title1' => 'Some data', 'title2' => 'Some other data');
     * Arrays::getNthKey($arr, 0) // title1
     * </code>
     *
     * @param int $num The nth key to get (starts from 0)
     *
     * @return mixed NULL if the nth key doesn't exist
     */
    public static function getNthKey($array, $num = 0)
    {
        if (sizeof($array) < $num) {
            return;
        }

        reset($array);
        for ($i = 0; $i < $num; ++$i) {
            next($array);
        }
        $k = key($array);

        return $k;
    }

    /**
     * Get the Nth item in the array. Obviously only useful for
     * non-numerical indexed arrays.
     *
     * <code>
     * $arr = array('title1' => 'Some data', 'title2' => 'Some other data');
     * Arrays::getNthItem($arr, 0) // Some data
     * </code>
     *
     * @param int $num The nth key to get (starts from 0)
     *
     * @return mixed NULL if the nth item doesn't exist
     */
    public static function getNthItem($array, $num = 0)
    {
        $k = self::getNthKey($array, $num);

        if ($k === null) {
            return;
        }

        return $array[$k];
    }

    /**
     * Get the first key of an array.
     *
     * @param array $array
     *
     * @return int index
     */
    public static function getFirstKey($array)
    {
        return self::getNthKey($array, 0);
    }

    /**
     * Get the last key of an array.
     *
     * @param unknown_type $array
     *
     * @return unknown
     */
    public static function getLastKey($array)
    {
        return self::getNthKey($array, 0);
    }

    /**
     * Get the first item of an array.
     *
     * @param array $array
     *
     * @return mixed
     */
    public static function getFirstItem($array)
    {
        return self::getNthItem($array, 0);
    }

    /**
     * Get the last item of an array.
     *
     * @param array $array
     *
     * @return mixed
     */
    public static function getLastItem($array)
    {
        if ($array instanceof Collection) {
            return $array->last();
        }

        return self::getNthItem($array, sizeof($array) - 1);
    }

    /**
     * Check to see if an item is in an array. Just like in_array(), but works
     * with arrays of items to check instead of just a single value.
     *
     * If you want to search for an array within the array, you must wrap it in
     * an outer array so the function expects it properly: array($searchforthis)
     *
     * If $all is true, then the function will only return true if all of the items
     * in $items are found. If it is false, it will return true when any one of the items
     * is found.
     *
     * @param array $items  The items to search for
     * @param array $array  The array to search in
     * @param bool  $all    Search for all (true) or just any (false)
     * @param bool  $strict Use strict comparisons
     *
     * @return bool
     */
    public static function isIn($items, $array, $all = false, $strict = false)
    {
        if (!$array) {
            return false;
        }

        $items = (array) $items;

        foreach ($items as $val) {
            if (in_array($val, $array, $strict)) {
                if (!$all) {
                    return true;
                }
            } else {
                if ($all) {
                    return false;
                }
            }
        }

        if ($all) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if keys are in an array. Just like array_key_exists() or an isset(),
     * but works with arrays of keys instead of just one.
     *
     * If $all is true, then the function will only return true if all of the keys
     * in $keys are found. If it is false, it will return true when any one of the keys
     * is found.
     *
     * @param array $keys  The keys to search for
     * @param array $array The array to search in
     * @param bool  $all   Search for all (true) or just any (false)
     *
     * @return bool
     */
    public static function isKeyIn($keys, $array, $all = false)
    {
        if (!$array) {
            return false;
        }

        $keys = (array) $keys;

        foreach ($keys as $k) {
            if (isset($array[$k])) {
                if (!$all) {
                    return true;
                }
            } else {
                if ($all) {
                    return false;
                }
            }
        }

        if ($all) {
            return true;
        }

        return false;
    }

    /**
     * Search an entire array and return all keys that match a value. Just like
     * array_search() except this returns all keys, instead of just one.
     *
     * @param array $array  The array to search through
     * @param mixed $search The value to search for
     * @param bool  $strict True to enable strict comparisons
     *
     * @return array
     */
    public static function searchAll($array, $search, $strict = false)
    {
        $found_keys = [];

        if ($strict) {
            foreach ($array as $k => $v) {
                if ($search === $v) {
                    $found_keys[] = $k;
                }
            }
        } else {
            foreach ($array as $k => $v) {
                if ($search == $v) {
                    $found_keys[] = $k;
                }
            }
        }

        return $found_keys;
    }

    /**
     * Use a callback function to get the value of first match in an array.
     *
     * @param array    $array
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public static function findValue($array, $callback, $default = null)
    {
        foreach ($array as $k => $v) {
            if (call_user_func($callback, $v, $k)) {
                return $v;
            }
        }

        return $default;
    }

    /**
     * Use a callback function to find the key of the first match in an array.
     *
     * @param array    $array
     * @param callback $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public static function findKey($array, $callback, $default = null)
    {
        foreach ($array as $k => $v) {
            if (call_user_func($callback, $v, $k)) {
                return $k;
            }
        }

        return $default;
    }

    /**
     * Use the Symfony PropertyAccess Component to find a value in an array.
     *
     * You will not get an exception with this method if the path does not exist.
     *
     * @param array  $array
     * @param string $property_path
     * @param mixed  $not_found     the value returned if the property path does not exist
     *
     * @return mixed
     */
    public static function findPropertyPath(array $array, $property_path, $not_found = null)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        try {
            return $accessor->getValue($array, $property_path);
        } catch (AccessException $e) {
            return $not_found;
        }
    }

    /**
     * Takes an array and normalizes all keys to lowercase.
     *
     * @param array $array     The array to work on
     * @param int   $dupe_mode What to do when a lowercased key already exists (i.e., MyKey and mykey were in the original array)
     *
     * @return array
     */
    public static function lowercaseKeys($array, $dupe_mode = LOWERKEY_DUPE_OVERWRITE)
    {
        foreach ($array as $key => $value) {
            $lower_key = strtowloer($key);
            if ($lower_key == $key) {
                continue;
            } // already lowercase

            unset($array[$key]);

            // If we're adding up dupes
            if ($dupe_mode == self::LOWERKEY_DUPE_ADD_ARRAY) {
                // If theres only one, then no need for an array
                if (!isset($array[$lower_key])) {
                    $array[$lower_key] = $value;
                // Otherwise, if we already have an array, add the value to the collection
                } elseif (is_array($array[$lower_key])) {
                    $array[$lower_key][] = $value;
                // And lastly, we already have a value so we're making a new array
                } else {
                    $array[$lower_key] = [$array[$lower_key], $value];
                }
            // We dont care if there was an existing value or not
            } else {
                $array[$lower_key] = $value;
            }
        }

        return $array;
    }

    /**
     * Converts an array to a string of "equals lines".
     *
     * @param array $array
     *
     * @return string
     *
     * @see Strings::parseEqualsLines()
     */
    public static function toEqualsLines(array $array)
    {
        $lines = [];

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subv) {
                    $lines[] = "$k = $subv";
                }
            } else {
                $lines[] = "$k = $v";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Generates an md5 hash of an arrays data. This tries to normalize
     * things a bit, so things like arrays in a different order but same
     * data would generate the same hash.
     *
     * @param array $array
     *
     * @return string
     */
    public static function generateHash(array $array, $keys_significant = true)
    {
        return self::_generateHashHelper($array, $keys_significant);
    }

    protected static function _generateHashHelper(array $array, $keys_significant = true)
    {
        $data_str = [];

        if ($keys_significant) {
            ksort($array, SORT_REGULAR);
        } else {
            sort($array, SORT_REGULAR);
        }

        foreach ($array as $k => $v) {
            if ($keys_significant) {
                $data_str[] = $k;
            }

            switch (gettype($v)) {
                case 'array':
                    $v = self::_generateHashHelper($v, false);
                    break;

                case 'object':
                    if (method_exists($v, 'toString')) {
                        $v = $v->toString();
                    } elseif (method_exists($v, '__toString')) {
                        $v = $v->__toString();
                    } elseif (method_exists($v, 'toArray')) {
                        $v = $v->toArray();
                        $v = self::_generateHashHelper($v, false);
                    } else {
                        $v = serialize($v);
                        $v = md5($v);
                    }
                    break;

                case 'resource':
                    $v = 'resource';
                    break;
            }

            $data_str[] = $v;
        }

        $data_str = implode('', $data_str);

        return md5($data_str);
    }

    /**
     * Unset a specific deep value of an array.
     *
     * @param array $array The array to unset in
     * @paray  array $keys  The keys used to get to deep item to unset
     *
     * @return bool True if the unset was performed
     */
    public static function unsetKey(array &$array, $keys)
    {
        $keys = (array) $keys;

        // Top level, no looping needed
        if (count($keys) == 1) {
            $keys = array_pop($keys);
            unset($array[$keys]);

            return true;
        }

        $last_key = array_pop($keys);
        $subarray = &$array;

        foreach ($keys as $key) {
            if (!is_array($subarray) or !isset($subarray[$key])) {
                return false;
            }

            $subarray = &$subarray[$key];
        }

        unset($subarray[$last_key]);

        return true;
    }

    /**
     * Takes an array of error codes and flattens it into a
     * single-dimentional array, easy for testing in templates etc.
     *
     * <code>
     * $errors = array(
     *    'username' => array('required'),
     *    'title' => array('too_short', 'invalid_characters')
     * );
     *
     * $flat_errors = Arrays::flattenErrors($errors);
     * // array(
     * //    'username_required' => true,
     * //    'title_too_short' => true,
     * //    'title_invalid_characters' => true
     * // );
     *
     * if ($flat_errors['title_invalid_characters']) {} // etc
     * </code>
     *
     * @param array  $array   The array to flatten
     * @param string $add_any Adds a new 'any' code, useful when you want to indicate that *any* error happened
     *                        on a given field. For example a string value 'hasError' will add 'title_hasError' to true
     * @param string $prefix  Prefix all keys with this string
     *
     * @return array
     */
    public static function flattenCodeArray(array $array, $add_any = false, $prefix = '')
    {
        $new = [];

        foreach ($array as $k => $v) {
            // May be empty array
            if (!$v) {
                continue;
            }

            $k = $prefix.$k.'_';

            if ($add_any) {
                $new[$k.$add_any] = true;
            }

            foreach ($v as $code) {
                if (is_array($code)) {
                    $new = $new + self::flattenCodeArray($code, $add_any, $k);
                } else {
                    $new[$k.$code] = true;
                }
            }
        }

        return $new;
    }

    /**
     * Search for a value in an array and remove it.
     *
     * @param array $array  The array to work on
     * @param mixed $value  A value to search for and remove, or an array of values
     * @param bool  $strict Use strict (===) comparisons instead of weak (==)
     *
     * @return array
     */
    public static function removeValue(array $array, $value, $strict = false)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $v) {
            while (($k = array_search($v, $array, $strict)) !== false) {
                unset($array[$k]);
            }
        }

        return $array;
    }

    /**
     * Replace all $find values in the array with $replace.
     *
     * @param array  $value
     * @param string $find
     * @param string $replace
     *
     * @return array
     */
    public static function replaceValue(array $value, $find, $replace)
    {
        $new = $value;
        foreach ($new as &$v) {
            if ($v == $find) {
                $v = $replace;
            }
        }

        return $new;
    }

    /**
     * Replace all $key key values in the array with $replace recursively.
     *
     * @param array  $array
     * @param string $key
     * @param string $replace
     *
     * @return array
     */
    public static function replaceKeyWithValueRecursive(array $array, $key, $replace)
    {
        $new = [];

        foreach ($array as $k => $v) {
            if ((string) $k === (string) $key) {
                $v = $replace;
            } elseif (is_array($v)) {
                $v = self::replaceKeyWithValueRecursive($v, $key, $replace);
            }
            $new[$k] = $v;
        }

        return $new;
    }

    /**
     * Take an array of arrays, and merge each sub-array into one big one.
     *
     * <code>
     * $array_of_arrays = array(array('id1' => 'test'), array('id2' => 'test2'), array('id3' => 'test3'));
     * $array = Arrays::mergeSubArrays($array_of_arrays);
     * // -> array('id1' => 'test', 'id2' => 'test2', 'id3' => 'test3')
     * </code>
     *
     * @param array $array_of_arrays The array of arrays to merge
     * @param int   $levels          How deep to go in merging sub-arrays
     *
     * @return array
     */
    public static function mergeSubArrays(array $array_of_arrays, $levels = 1)
    {
        return self::_mergeSubArray_helper($array_of_arrays, $levels, 1);
    }

    protected static function _mergeSubArray_helper(array $array_of_arrays, $max_level, $cur_level)
    {
        $array = [];

        foreach ($array_of_arrays as $sub_array) {
            if ($cur_level < $max_level and is_array($sub_array)) {
                $sub_array = self::_mergeSubArray_helper($sub_array, $levels, $cur_level + 1);
            }

            $array = array_merge($array, $sub_array);
        }

        return $array;
    }

    /**
     * Same as PHP's shuffle() except works on assoc arrays by maintaining
     * indexes.
     *
     * @param array $array
     */
    public static function shuffleAssoc(array &$array)
    {
        if (!$array) {
            return false;
        }

        $old_array = $array;
        $array     = [];
        $keys      = array_keys($old_array);
        shuffle($keys);

        foreach ($keys as $k) {
            $array[$k] = $old_array[$k];
        }

        return true;
    }

    /**
     * Given an array of items, split them into pages and get a certain chunk of them.
     *
     * For example, given an array of 1000 ID's of results, easily fetch groups of 25
     * results from it.
     *
     * @param array $array
     * @param int   $page
     * @param int   $per_page
     *
     * @return array
     */
    public static function getPageChunk(array $array, $page, $per_page)
    {
        // aka "unlimited per page"
        if (!$per_page) {
            return $array;
        }

        $count = count($array);

        $page  = max(0, $page);
        $start = ($page - 1) * $per_page;

        // Invalid page
        if ($start > $count) {
            return [];
        }

        return array_slice($array, $start, $per_page);
    }

    /**
     * Use a key in an array of arrays as a grouping variable.
     *
     * <code>
     * $array = array(
     *     array('type' => 'foo', 'name' => 'Example 1'),
     *     array('type' => 'foo', 'name' => 'Example 2'),
     *     array('type' => 'bar', 'name' => 'Example 3'),
     *     array('type' => 'bar', 'name' => 'Example 4'),
     * );
     *
     * $array = Arrays::groupItems($array);
     * // array(
     * //     'foo' => array(
     * //         array('type' => 'foo', 'name' => 'Example 1'),
     * //         array('type' => 'foo', 'name' => 'Example 2'),
     * //     ),
     * //     'bar' => array(
     * //         array('type' => 'bar', 'name' => 'Example 3'),
     * //         array('type' => 'var', 'name' => 'Example 4'),
     * //     ),
     * // );
     * </code>
     *
     * @param array    $array            The array to work on
     * @param string   $group_key        The key in the array that serves as the grouping value
     * @param bool     $preserve_keys    True to preserve keys when grouping
     * @param callback $mutator_callback A callback function to call on the group to normalize the group value
     *
     * @return array
     */
    public static function groupItems($array, $group_key, $preserve_keys = false, $mutator_callback = null)
    {
        $ret = [];

        foreach ($array as $k => $v) {
            $group = $v[$group_key];

            if ($mutator_callback) {
                $group = $mutator_callback($group);
            }

            if (!isset($ret[$group])) {
                $ret[$group] = [];
            }

            if ($preserve_keys) {
                $ret[$group][$k] = $v;
            } else {
                $ret[$group][] = $v;
            }
        }

        return $ret;
    }

    /**
     * Run a callback test on all items of an array, and return true if
     * every item of the array passes.
     *
     * @param  $array
     * @param  $callback
     *
     * @return bool
     */
    public static function checkAll($array, $callback)
    {
        foreach ($array as $k => $v) {
            if (!call_user_func($callback, $v, $k)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Like array_walk() but the callback can take the key by reference to change it.
     *
     * @param  $array
     * @param  $callback function ($key, $value)
     *
     * @return array
     */
    public static function walkKeys($array, $callback)
    {
        $keys     = array_keys($array);
        $new_keys = [];

        foreach ($keys as $k) {
            call_user_func($callback, $k, $array[$k]);
            $new_keys[] = $k;
        }

        $values = array_values($array);

        $array = array_combine($new_keys, $values);

        return $array;
    }

    /**
     * Splices an assoc array and maintains keys.
     *
     * @param $array
     * @param $start
     * @param null $length
     *
     * @return array
     */
    public static function spliceAssoc($array, $start, $length = null)
    {
        $new_array = [];

        foreach ($array as $k => $v) {
            if ($start) {
                --$start;
                continue;
            }

            $new_array[$k] = $v;

            if ($length !== null) {
                --$length;
                if ($length == 0) {
                    break;
                }
            }
        }

        return $new_array;
    }

    /**
     * Sort an array into an alphabetical index.
     *
     * Returns:
     * <code>array('a' => array(...), 'b' => array(...))</code>
     *
     * The $array passed can be a single-dimentional array, or a multi-dimentional array:
     *
     * <code>
     * $array = array('apple', 'banana', 'zebra');
     * $alpha_array = Arrays::sortIntoAlphabeticalIndex($array);
     *
     * $array = array(44 => array('id' => 44, 'word' => 'apple'), 71 => array('id' => 71, 'word' => 'zebra'));
     * $alpha_array = Arrays::sortIntoAlphabeticalIndex($array);
     * </code>
     *
     * @param array $array         The array
     * @param mixed $word_index    If items in $array is itself an array, the index that contains the word
     * @param bool  $empty_letters True to include empty letters in the array (the letters will themselves be emtpy arrays)
     *
     * @return array
     */
    public static function sortIntoAlphabeticalIndex(
        $array,
        $word_index = null,
        $maintain_keys = false,
        $empty_letters = false
    ) {
        $aindex = [];

        if ($empty_letters) {
            $aindex = ['@' => [], '#' => []];
            foreach (range('A', 'Z') as $l) {
                $aindex[$l] = [];
            }
        }

        foreach ($array as $k => $item) {
            if (is_array($word_index)) {
                $label = $item[$word_index];
            } else {
                $label = $item;
            }

            $first = Strings::utf8_substr($label, 0, 1);
            $first = Strings::utf8_accents_to_ascii($first);
            $first = Strings::utf8_strtoupper($first);

            if (is_numeric($first)) {
                $first = '#';
            } elseif (!preg_match('#[A-Z]#', $first)) {
                $first = '@';
            }

            if (!isset($aindex[$first])) {
                $aindex[$first] = [];
            }

            if ($maintain_keys) {
                $aindex[$first][$k] = $item;
            } else {
                $aindex[$first][] = $item;
            }
        }

        ksort($aindex, SORT_STRING);

        // Put @ before #,
        if (isset($aindex['@']) and isset($aindex['#'])) {
            $a = $aindex['@'];
            $b = $aindex['#'];

            unset($aindex['@'], $aindex['#']);

            self::unshiftAssoc($aindex, '#', $b);
            self::unshiftAssoc($aindex, '@', $a);
        }

        return $aindex;
    }

    /**
     * Sort an array by the value i a sub-array.
     *
     * @param array $array
     * @param int   $sort_flags
     */
    public static function sortMulti(array &$array, $k, $sort_flags = \SORT_REGULAR)
    {
        usort(
            $array,
            function ($a, $b) use ($k, $sort_flags) {
                $a = $a[$k];
                $b = $b[$k];

                if ($sort_flags == \SORT_NUMERIC) {
                    $a += 0.0;
                    $b += 0.0;
                } elseif ($sort_flags == \SORT_STRING) {
                    $a .= '';
                    $b .= '';
                }

                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            }
        );
    }

    /**
     * Counts an array of arrays, returning the grand total.
     * This does not descend past 1 level. Try valueCount to count all values in an array.
     *
     * @param array $array
     *
     * @return int
     */
    public static function countMulti(array &$array)
    {
        $count = 0;

        foreach ($array as $sub) {
            $count += count($sub);
        }

        return $count;
    }

    /**
     * Go down an array and count each leaf to get a grand total of the number of values
     * in the array.
     *
     * @param array $array
     *
     * @return int
     */
    public static function valueCount(array $array)
    {
        $count = 0;

        foreach ($array as $sub) {
            if (is_array($sub)) {
                $count += self::valueCount($sub);
            } else {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Take an array of ordered elements (usually IDs) that are keys in $unordered_data,
     * and create a new array where all data is in the same order as $ordered_ids.
     *
     * @param array $ordered_ids
     * @param array $unordeded_data
     * @param bool  $append_remain  True to append any remaining elements in $unordered_data if there are any
     *
     * @return array
     */
    public static function orderIdArray(array $ordered_ids, array $unordeded_data, $append_remain = false)
    {
        $data = [];

        foreach ($ordered_ids as $id) {
            if (isset($unordeded_data[$id])) {
                $data[$id] = $unordeded_data[$id];
            }
        }

        if ($append_remain && count($ordered_ids) != count($unordeded_data)) {
            $keys        = array_keys($unordeded_data);
            $append_keys = array_diff($keys, $ordered_ids);

            if ($append_keys) {
                foreach ($append_keys as $k) {
                    $data[$k] = $unordeded_data[$k];
                }
            }
        }

        return $data;
    }

    /**
     * Just like array_filter except you also get passed the current key as the second parameter.
     *
     * @param array    $array
     * @param callback $fn
     *
     * @return array
     */
    public static function filter(array $array, $fn)
    {
        $new_array = [];

        foreach ($array as $k => $v) {
            if ($fn($v, $k) !== false) {
                $new_array[$k] = $v;
            }
        }

        return $new_array;
    }

    /**
     * Like array_map except will run recursively on sub-arrays.
     *
     * @param array $array
     * @param $fn
     *
     * @return array
     */
    public static function mapRecursive(array $array, $fn)
    {
        $new_array = [];

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $new_array[$k] = self::mapRecursive($v, $fn);
            } else {
                $new_array[$k] = call_user_func($fn, $v);
            }
        }

        return $new_array;
    }

    /**
     * Like array_map except works with iterators.
     *
     * @param                    $fn
     * @param array|\Traversable $array
     *
     * @return array
     */
    public static function map($fn, $array)
    {
        if (!is_array($array)) {
            $array = iterator_to_array($array);
        }

        return array_map($fn, $array);
    }

    /**
     * Takes a simple stdClass and returns an array with its properties -> values.
     *
     * @param \stdClass|mixed $object
     *
     * @return array
     */
    public static function fromStdClass($object)
    {
        if (is_object($object)) {
            $object = get_object_vars($object);
        }

        if (is_array($object)) {
            return array_map(__METHOD__, $object);
        } else {
            return $object;
        }
    }

    /**
     * Given an array of k=>v, return array(array(k, v)).
     *
     * @param array|\Traversable $array
     * @param string|null        $k_name Optionally specify a key for the 'key' value
     * @param string|null        $v_name Optionally specify a key for the 'value' value
     *
     * @return array
     */
    public static function kvpairs($array, $k_name = null, $v_name = null)
    {
        $pairs = [];

        if ($array === null) {
            return [];
        }

        if (!is_array($array)) {
            $array = [$array];
        }

        foreach ($array as $k => $v) {
            if ($k_name || $v_name) {
                $pairs[] = [$k_name => $k, $v_name => $v];
            } else {
                $pairs[] = [$k, $v];
            }
        }

        return $pairs;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (! static::accessible($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?: $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
