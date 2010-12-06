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

namespace Application\DeskPRO;
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



	/**
	 * Tries to build an array of person data using an arbitrary array.
	 * This is used in the default usersource handlers and scraper handlers.
	 *
	 * @param array $misc_data
	 * @return array
	 */
public function getPersonData(array $misc_data)
	{
		$person_data = array(
			'standard_fields' => array(),
			'emails' => array(),
			'fields' => array()
		);

		$keymap = array(
			'full_name' => 'name',
			'name' => 'name',
			'fullname' => 'name',
			'nickname' => 'name',
			'nick_name' => 'name',
			'username' => 'name',
			'user_name' => 'name',
			'screen_name' => 'name',
			'screenname' => 'name',
			'first_name' => 'first_name',
			'firstname' => 'first_name',
			'last_name' => 'last_name',
			'lastname' => 'last_name',
		);

		foreach ($keymap as $findkey => $personkey) {
			if (isset($misc_data[$findkey])) {
				$person_data['standard_fields'][$personkey] = $misc_data[$findkey];
			}
		}

		$emailkeymap = array(
			'email', 'emails', 'email_address', 'emailaddress',
			'email_addresses', 'emailaddresses',
			'mail'
		);

		$scraper_emails = array();
		foreach ($emailkeymap as $findkey) {
			if (isset($misc_data[$findkey])) {
				$scraper_emails = array_merge($scraper_emails, $misc_data[$findkey]);
			}
		}

		$person_data['emails'] = $misc_data;

		return $person_data;
	}
}