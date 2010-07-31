<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Util;


/**
 * General utility functions.
 *
 * @static
 */
class Util
{
	const BASE62_ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	const BASE36_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

	/**
	 * Return $param if it is set, else return $or.
	 *
	 * Example:
	 * <code>
	 * // The following two lines are the same
	 * $val = isset($var) ? $var : 'default value';
	 * $val = Orb_Util::ifsetor($var, 'default value');
	 * </code>
	 *
	 * @param    mixed    $param    The parameter to check
	 * @param    mixed    $or       The value to return if $param is not set
	 * @return   mixed
	 */
	public static function ifsetor(&$param, $or = null)
	{
		if (isset($param)) {
			return $param;
		}

		return $or;
	}



	/**
	 * Return $param if it is truthy, else return $or.
	 *
	 * Example:
	 * <code>
	 * // The following two lines are the same
	 * $val = $var ? $var : 'val';
	 * $val = Orb_Util::ifvalor($var, 'val');
	 * </code>
	 *
	 * @param    mixed    $param    The parameter to check
	 * @param    mixed    $or       The value to return if $param is not truthy
	 * @return   mixed
	 */
	public static function ifvalor($param, $or)
	{
		if ($param) {
			return $param;
		}
		return $or;
	}



	/**
	 * Assigns a default value to a varaible if it has not been set or it is
	 * not truthy.
	 *
	 * Example:
	 * <code>
	 * // The following two lines are the same
	 * $val = (isset($val) AND $val) ? $val : 'default';
	 * Orb_Util::defaultVal($val, 'default');
	 * </code>
	 *
	 * @param    string   $param    The parameter to use
	 * @param    string   $default  The default value
	 */
	public static function defaultVal(&$param, $default = null)
	{
		if (!isset($param) or !$param) {
			$param = $default;
		}
	}



	/**
	 * Returns either $true or $false depending on if $cond evaluates
	 * to true/false.
	 *
	 * NOTE: You should use the ternary operator in most cases, but this function
	 * exists for situations where a function is required.
	 *
	 * @param    mixed    $cond    The condition
	 * @param    mixed    $true    What to return if the condition is true
	 * @param    mixed    $false   What to return if the condition is false
	 * @return   mixed
	 */
	public static function iff($cond, $true = true, $false = false)
	{
		return $cond ? $true : $false;
	}



	/**
	 * Return the first truthy value in all arguments. If no arguments are
	 * truthy, then the last argument is returned.
	 *
	 * // Examples
	 * <code>
	 * $var = Orb_Util::coalesce(false, null, true, 1); // true
	 * $var = Orb_Util::coalesce(false, null, array()); // array, because it's the last
	 * </code>
	 *
	 * @return mixed
	 */
	public static function coalesce()
	{
		foreach (func_get_args() as $v) {
			if ($v) {
				return $v;
			}
		}

		return func_get_arg(func_num_args() - 1);
	}



	/**
	 * Encode a number using an alphabet.
	 *
	 * @param   int     $num        The number to encode
	 * @param   string  $alphabet   The alphabet to encode with
	 * @return  string
	 */
	public static function baseEncode($num, $alphabet)
	{
		if ($num == 0) {
			return $alphabet[0];
		}

		$arr = array();
		$base = strlen($alphabet);

		while ($num) {
			$rem = $num % $base;
			$num = (int)($num / $base);
			$arr[] = $alphabet[$rem];
		}

		$arr = array_reverse($arr);
		return implode('', $arr);
	}



	/**
	 * Decode a number using an alphabet.
	 *
	 * @param   string  $string    The string-encoded number to decode
	 * @param   stirng  $alphabet  The alphabet used to decode
	 * @return  int
	 */
	public static function baseDecode($string, $alphabet)
	{
		$alphabet = str_split($alphabet);
		$base = sizeof($alphabet);
		$strlen = strlen($string);
		$num = 0;
		$idx = 0;

		$s = str_split($string);
		$tebahpla = array_flip($alphabet);

		foreach ($s as $char) {
			$power = ($strlen - ($idx + 1));
			$num += $tebahpla[$char] * (pow($base, $power));
			$idx += 1;
		}
		return $num;
	}



	/**
	 * Serialize a data structure and sign it with some secret key. The data
	 * is also base64.
	 *
	 * This is ideal when transmitting a serialized object where it could potentially
	 * be tampered with by a user. If they tamper with the data, then the sign hash
	 * becomes invalid and the unserialize method will throw an exception.
	 *
	 * @param  mixed   $data      The data you want to serialize (i.e., an array)
	 * @param  string  $sign_key  The secret key to sign with. You should most certainly provide this!
	 * @return string
	 */
	public static function signedSeriaize($data, $sign_key = 'orb_util_sign_key')
	{
		$ser = base64_encode(serialize($data));
		$md5 = md5($sign_key . $ser);

		// the :b64: part is so if in the future we change the encoding method,
		// the unserialize method below can be backwards compat by reading the b64 label
		return $md5 . ':b64:' . $ser;
	}



	/**
	 * Unserialized a signed serialized string.
	 *
	 * @see Orb_Util::signedSeriaize()
	 * @param  mixed   $string    The string you want to unserialize
	 * @param  string  $sign_key  The secret key it was signed with. You should most certainly provide this!
	 * @return mixed
	 * @throws Exception
	 */
	public static function signedUnserialize($string, $sign_key = 'orb_util_sign_key')
	{
		$md5 = substr($string, 0, 32);
		$ser = substr($string, 37);

		$md5_check = md5($sign_key . $ser);
		if ($md5 != $md5_check) {
			throw new Exception('Invalid data or sign key.');
		}

		return @unserialize(@base64_decode($ser));
	}
}
