<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage Import
 */

namespace Application\DeskPRO\People\PasswordScheme;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PasswordSchemeInterface;

class Deskpro3 implements PasswordSchemeInterface
{
	public function checkInput($plain_password, $hashed_password)
	{
		return false; // unsupported
	}

	public function hashPassword(Person $person, $plain_password)
	{
		if ($person->password_scheme == 'deskpro3_tech') {
			return sha1($plain_password . $person->salt);
		} else {
			return md5($plain_password . $person->salt);
		}
	}

	public function checkPassword(Person $person, $hashed_password, $plain_password)
	{
		return ($hashed_password === $this->hashPassword($person, $plain_password));
	}
}
