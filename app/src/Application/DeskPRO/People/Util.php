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
 * @subpackage People
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
