<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

/**
 * Utility functions that work with numbers.
 *
 * @static
 */
class Numbers
{
	const ROUND_MULTIPLE_NEAR = 1;
	const ROUND_MULTIPLE_UP   = 2;
	const ROUND_MULTIPLE_DOWN = 3;



	/**
	 * Check if a value is an integer value. This is like is_int() but also passes
	 * strings that are integer form.
	 *
	 * You can't use ctype_digit or is_numeric because they'll pass other numeric
	 * forms, not just integers.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function isInteger($value)
	{
		if (is_int($value) OR (int)$value == $value) {
			return true;
		}

		return false;
	}


	
	/**
	 * Check if something is somewhere within the range of two numbers.
	 *
	 * @param    int    $what   The thing to check
	 * @param    int    $min    The minimum number (inclusive)
	 * @param    int    $max    The maximum number (inclusive)
	 * @return   bool
	 */
	public static function inRange($what, $min = 0, $max = 10)
	{
	    if ($what >= $min AND $what <= $max) {
	        return true;
	    }

	    return false;
	}



	/**
	 * Turn a number into roman numerals.
	 *
	 * <code>
	 * echo Orb_Num::romanNumerals(42); // XLII
	 * </code>
	 *
	 * @param  int  $num  The number to romanize
	 * @return string
	 */
	public static function romanNumerals($num)
	{
		static $map = array(
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1,
		);

		$num = intval($num);
		$res = '';

		foreach ($map as $roman => $value) {
			$res .= str_repeat($roman, (int)$num/$value);
			$num %= $value;
		}

		return $res;
	}



	/**
	 * Display a filesize in bytes in the smallest unit.
	 *
	 * @param int $bytes
	 * @return string
	 */
	public static function filesizeDisplay($bytes)
	{
	    if (!$bytes OR $bytes < 1) {
	        return '0 B';
	    }

	    $all_symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $exp = floor(log($bytes)/log(1024));
        $val = $bytes/pow(1024, floor($exp));

        $sym = '';
        if (isset($all_symbols[$exp])) {
            $sym = $all_symbols[$exp];
        }

        return sprintf('%.2f %s', $val, $sym);
	}



	/**
	 * Round a number to the nearest multiple.
	 *
	 * <code>
	 * echo Orb_Num::roundToMultiple(26, 5); // 25
	 * echo Orb_Num::roundToMultiple(26, 5, Orb_Num::ROUND_MULTIPLE_UP); // 30
	 * echo Orb_Num::roundToMultiple(29, 5, Orb_Num::ROUND_MULTIPLE_DOWN); // 25
	 * </code>
	 *
	 * @param  int|float  $number     The number to round
	 * @param  int        $multiple   The multiple to round to
	 * @param  int        $mode       Rounding mode to use
	 * @return mixed
	 */
	public static function roundToMultiple($number, $multiple, $mode = self::ROUND_MULTIPLE_NEAREST)
	{
		if ($mode == self::ROUND_MULTIPLE_NEAR) {
			return round($number / $multiple) * $multiple;
		} elseif ($mode == self::ROUND_MULTIPLE_DOWN) {
			return floor(floor($number) / $multiple) * $multiple;
		} else {
			return ceil(ceil($number) / $multiple) * $multiple;
		}
	}
}