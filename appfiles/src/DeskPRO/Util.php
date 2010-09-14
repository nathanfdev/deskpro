<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Util
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A simple utility class.
 *
 * @static
 */
class Util
{
	final private function __construct() { /* This class is never instantiated */ }

	/**
	 * Create a new object
	 * 
	 * @param string $classname_spec  The classname, static factory method, or array callback
	 * @param array  $options         Options to pass to the factory or constructor
	 * @return object
	 */
	public static function simpleObjectFactory($classname_spec, array $options = null)
	{
		if ($options === null) $options = array();

		// A static factory like SomeClass::getSomeObject(options)
		if (is_array($classname_spec) OR strpos($classname_spec, '::')) {
			$obj = call_user_func($classname_spec, $options);
		} else {
			$obj = new $classname_spec($options);
		}

		return $obj;
	}
}