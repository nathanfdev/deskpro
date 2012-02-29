<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */
namespace Application\DeskPRO\People;

class Util
{
	private function __construct() {}

	/**
	 * Takes a full name and an email address and tries to parse out a first and last name.
	 *
	 * @param string|null $full_name
	 * @param string|null $email_address
	 * @return array
	 */
	public static function guessNameParts($full_name = null, $email_address = null)
	{
		if (!$full_name && !$email_address) {
			return array('', '');
		}

		$first_name = null;
		$last_name = null;

		if ($full_name && strpos($full_name, ' ') !== false) {
			list ($first_name, $last_name) = explode(' ', $full_name, 2);
		} elseif ($email_address) {
			list ($email_name,) = explode('@', $email_address, 2);
			if (strpos($email_name, '.') !== false) {
				list ($first_name, $last_name) = explode('.', $email_name, 2);
				$first_name = ucfirst($first_name);
				$last_name = ucfirst($last_name);
			}
		}

		// Just set the first name to whatever we might have, its the best we can do
		if (!$first_name && !$last_name) {
			if ($full_name) {
				$first_name = $full_name;
			} elseif ($email_address) {
				list ($email_name,) = explode('@', $email_address, 2);
				$first_name = ucfirst($email_name);
			}
		}

		return array(
			$first_name,
			$last_name
		);
	}
}
