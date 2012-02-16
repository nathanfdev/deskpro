<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;

interface PasswordSchemeInterface
{
	/**
	 * @param $plain_password
	 * @return string
	 */
	public function hashPassword(Person $person, $plain_password);
}
