<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Util
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

	/**
	 * @param array $ordered_ids
	 * @return void
	 */
	public static function updateDisplayOrders(array $ordered_ids, $table)
	{
		$o = 10;

		App::getDb()->beginTransaction();

		foreach ($ordered_ids as $id) {
			App::getDb()->update($table, array('display_order' => $o), array('id' => $id));

			$o += 10;
		}

		App::getDb()->commit();
	}

	public static function getPrintableTimeLength($length)
	{
		if ($length < 1) {
			return '';
		}

		if ($length > 3600) {
			$hours = floor($length / 3600);
			$length -= $hours * 3600;
		} else {
			$hours = 0;
		}

		if ($length > 60) {
			$minutes = floor($length / 60);
			$length -= $minutes * 60;
		} else {
			$minutes = 0;
		}

		$seconds = $length;

		// TODO: translation
		$parts = array();
		if ($hours) {
			$parts[] = ($hours > 1 ? "$hours hours" : '1 hour');
		}
		if ($minutes) {
			$parts[] = ($minutes > 1 ? "$minutes minutes" : '1 minute');
		}
		if ($seconds) {
			$parts[] = ($seconds > 1 ? "$seconds seconds" : '1 second');
		}

		return implode(', ', $parts);
	}
}
