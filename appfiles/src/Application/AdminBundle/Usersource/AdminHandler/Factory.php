<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AgentBundle\Usersource\AdminHandler;

use \Application\DeskPRO\Entity\Usersource;

/**
 * Creates an admin handler based off of a particular usersource.
 * 
 * @static
 */
class Factory
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\AgentBundle\CustomField\AdminHandler\AbstractAdminHandler
	 */
	public static function createUsersource(Usersource $usersource)
	{
		$handler = null;
		switch ($usersource['handler_class']) {
			case 'Application\\DeskPRO\\Usersource\\Handler\\Twitter':
				$handler = new \Application\AgentBundle\Usersource\AdminHandler\Twitter($usersource);
				break;

			case 'Application\\DeskPRO\\Usersource\\Handler\\OpenId':
				$handler = new \Application\AgentBundle\Usersource\AdminHandler\OpenId($usersource);
				break;
		}
		
		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$usersource['handler_class']}");
		}

		return $handler;
	}
}