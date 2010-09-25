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



	/**
	 * Create a new object and pass $args as arguments to the constructor.
	 * Same as callUserConstructor but this takes an array of arguments instead.
	 * 
	 * @param string $classname  The class to instantiate
	 * @param array  $args       Args to pass to the constructor
	 * @return $classname
	 */
	public static function callUserConstructorArray($classname, array $args)
	{
		switch (count($args)) {
			// Most constructors wont take any more than a handful arguments
			case 0:  $obj = new $classname();
			case 1:  $obj = new $classname($args[0]); break;
			case 2:  $obj = new $classname($args[0], $args[1]); break;
			case 3:  $obj = new $classname($args[0], $args[1], $args[2]); break;
			case 4:  $obj = new $classname($args[0], $args[1], $args[2], $args[3]); break;
			case 5:  $obj = new $classname($args[0], $args[1], $args[2], $args[3], $args[4]); break;
			case 6:  $obj = new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
			case 7:  $obj = new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]); break;
			case 8:  $obj = new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]); break;
			case 9:  $obj = new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]); break;

			// But if there's more, fallback on reflection
			default:
				$ref = new ReflectionClass($classname);
				$obj = $ref->newInstance($args);
				break;
		}

		return $obj;
	}

	

	/**
	 * Create a new object and pass arguments to the constructor.
	 *
	 * @param string $classname  The class to instantiate
	 * @param mixed  $param...   Parameters to call the constructor with
	 * @return $classname
	 */
	public static function callUserConstructor($classname)
	{
		$args = func_get_args();
		array_shift($args); // get rid of $classname

		return self::callUserConstructorArray($classname, $args);
	}



	/**
	 * An integer that is guarenteed to be unique for this one request.
	 * It's simply a global counter.
	 *
	 * @return int
	 */
	public static function requestUniqueId()
	{
		static $x = 0;

		return ++$x;
	}



	/**
	 * Generate a random security token using some secret.
	 *
	 * @param string  $secret   A secret to encode the token with.
	 * @param int     $timeout  How long (seconds) is the token valid for? 0 disables
	 * @return string
	 */
	public static function generateStaticSecurityToken($secret, $timeout = 0)
	{
		if ($timeout) {
			// rand is so we never give the exact real time the token was made
			// since we have to put that in plaintext
			$expire_time = time() + $timeout + mt_rand(1, 10);
			$expire_time_enc = base_convert($expire_time, 10, 36);
		} else {
			$expire_time = 0;
			$expire_time_enc = 0;
		}

		$rand_str = Strings::random(10, Strings::CHARS_ALPHA_I);

		$token = $expire_time_enc . '-' . $rand_str . '-' . sha1($secret . $expire_time_enc . $rand_str);

		return $token;
	}
	


	/**
	 * Check a security token to see if its valid.
	 *
	 * @param string $token   The token to check
	 * @param string $secret  The same secret used to create the token
	 * @return bool
	 */
	public static function checkStaticSecurityToken($token, $secret)
	{
		// Check to make sure its a valid format
		if (substr_count($token, '-') != 2) {
			return false;
		}

		list($expire_time_enc, $rand_str, $hash) = explode('-', $token, 3);

		// Check the hash first
		$check_hash = sha1($secret . $expire_time_enc . $rand_str);

		if ($check_hash != $hash) {
			return false;
		}

		// Check the time now
		if ($expire_time_enc != '0') {
			$expire_time = base_convert($expire_time_enc, 36, 10);
			if (time() > $expire_time) {
				return false;
			}
		}

		return true;
	}
}
