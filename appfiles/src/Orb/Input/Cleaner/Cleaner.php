<?php
/**
 * Orb
 *
 * @package Orb
 * @category Input
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Input\Cleaner;


/**
 * A cleaner class that cleans any input to fit a data type.
 */
class Cleaner
{
	/**
	 * A map of aliases to a cleaner type
	 * @var array
	 */
	protected $string_type_map = array(
		'discard'       => 'discard',
		'raw'           => 'raw',
		'bool'          => 'bool',
		'boolean'       => 'bool',
		'bool_int'      => 'bool_int',
		'ibool'         => 'bool_int',
		'int'           => 'int',
		'integer'       => 'int',
		'uint'          => 'uint',
		'float'         => 'float',
		'ufloat'        => 'ufloat',
		'num'           => 'num',
		'number'        => 'num',
		'unum'          => 'unum',
		'str'           => 'str',
		'string'        => 'str',
		'str_notrim'    => 'str_notrim',
		'str_nohtml'    => 'str_nohtml',
		'nohtml'        => 'str_nohtml',
		'str_striphtml' => 'str_striphtml',
		'striphtml'     => 'str_striphtml',
		'str_simple'    => 'str_simple',
		'simplestr'     => 'str_simple',
		'str_raw'       => 'str_raw',
		'rawstr'        => 'str_raw',
		'rawstring'     => 'str_raw',
	);



	/**
	 * Clean a value.
	 *
	 * @param   mixed  $value    The value to clean
	 * @param   int    $type     The type to cast to
	 * @param   mixed  $options  Options for the type
	 * @return  mixed  The cleaned value
	 */
	public function clean($value, $type = 'raw', $options = null)
	{
		if (isset($this->string_type_map[$type])) {
			$type = $this->string_type_map[$type];
		} else {
			throw new \InvalidArgumentException("Invalid cleaner type `$type`");
		}


		#----------------------------------------
		# Do the cleaning
		#----------------------------------------

		switch ($type) {
			case 'bool':
				$value = (bool)$value;
				break;

			case 'bool_int':
				$value = (int)((bool)$value);
				break;

			case 'int':
				$value = (int)$value;
				break;

			case 'uint':
				$value = (int)$value;

				if ($value < 0) {
					$value = 0;
				}
				break;

			case 'num':
				$value = ((string)$value) + 0;
				break;

			case 'unum':
				$value = ((string)$value) + 0;

				if ($value < 0) {
					$value = 0;
				}
				break;

			case 'str':
				$value = trim($this->cleanUtf8($value));
				break;

			case 'str_notrim':
				$value = (string)$this->cleanUtf8($value);
				break;

			case 'str_nohtml':
				$value = htmlspecialchars(trim($this->cleanUtf8($value)));
				break;

			case 'str_striphtml':
				$value = strip_tags(trim($this->cleanUtf8($value)));
				break;

			case 'str_simple':
				$value = preg_replace('#[^a-zA-Z0-9 _\-\.:]#', '', trim($this->cleanUtf8($value)));
				break;

			case 'str_raw':
				$value = (string)$value;
				break;
		}

		return $value;
	}



	/**
	 * Clean an array of values. $type_key can be TYPE_DISCARD if you dont want to keep the keys. In such cases,
	 * the array indecies will be integers (i.e., array will be build via $array[]=$val).
	 *
	 * @param   array    $array        The array to clean
	 * @param   integer  $type_val     The type to cast values to
	 * @param   integer  $type_key     The type to cast keys to
	 * @param   mixed    $options_val  Options for the val type
	 * @param   mixed    $options_key  Options for the key type
	 * @return  array
	 */
	public function cleanArray($array, $type_val = self::TYPE_RAW, $type_key = self::TYPE_RAW, $options_val = null, $options_key = null)
	{
	    if (!is_array($array)) {
	        $array = (array)$array;
	    }

	    $ret_array = array();

	    $type_val = $this->stringTypeToInt($type_val);
	    $type_key = $this->stringTypeToInt($type_key);

		foreach ($array as $k => $v) {
			$k = $this->clean($k, $type_key, $options_key);
			$v = $this->clean($v, $type_val, $options_val);

			if ($type_key == self::TYPE_DISCARD) {
				$ret_array[] = $v;
			} else {
				$ret_array[$k] = $v;
			}
		}

		return $ret_array;
	}



	/**
	 * If a string has mb characters, this will ensure the string is well-formed and
	 * fix it if it's not (most secure thing to do).
	 *
	 * This uses phputf8 from sourceforge if it's available. If it's not available,
	 * this cleaner does nothing.
	 * 
	 * @link http://sourceforge.net/projects/phputf8/
	 *
	 * @param string|array $string The string to work on, or an array to go through
	 * @return string
	 */
	public function cleanUtf8($string)
	{
		static $has_utf8_funcs = null;
		if ($has_utf8_funcs === null) {
			$has_utf8_funcs = function_exists('utf8_is_ascii');
		}

		if (!$has_utf8_funcs) {
			return $string;
		}

		#-------------------------
		# Recursively clean arrays
		#-------------------------

		if (is_array($string)) {
			foreach ($string as $k => $v) {
				$k = $this->cleanUtf8($k);
				$v = $this->cleanUtf8($v);

				$string[$k] = $v;
			}

			return $string;
		}


		#-------------------------
		# Clean normal strings
		#-------------------------

		if (!is_string($string)) {
			return $string;
		}

		if (!utf8_is_ascii($string) AND !utf8_is_valid($string)) {
			$string = utf8_bad_strip($string);
		}

		return $string;
	}
}