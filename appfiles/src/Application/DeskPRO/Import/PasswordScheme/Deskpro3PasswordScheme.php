<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\PasswordScheme;

use Application\DeskPRO\People\PasswordSchemeInterface;
use Application\DeskPRO\Entity\Person;

class Deskpro3PasswordScheme implements PasswordSchemeInterface
{
	public function hashPassword(Person $person, $plain_password)
	{
		if ($person->password_scheme == 'deskpro3_tech') {
			return sha1($plain_password . $person->salt);
		} else {
			return md5($plain_password . $person->salt);
		}
	}
}
