<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Usersource\AdminHandler;

use \Application\CoreBundle\Entity\Usersource;

/**
 * Creates an admin handler based off of a particular usersource.
 * 
 * @static
 */
class Factory
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\TechBundle\CustomField\AdminHandler\AbstractAdminHandler
	 */
	public static function createUsersource(Usersource $usersource)
	{
		$handler = null;
		switch ($usersource['handler_class']) {
			case 'DeskPRO\\Usersource\\Handler\\Twitter':
				$handler = new \Application\TechBundle\Usersource\AdminHandler\Twitter($usersource);
				break;

			case 'DeskPRO\\Usersource\\Handler\\OpenId':
				$handler = new \Application\TechBundle\Usersource\AdminHandler\OpenId($usersource);
				break;
		}
		
		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$usersource['handler_class']}");
		}

		return $handler;
	}
}