<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Usersource\Handler;

use Application\CoreBundle\Entity\Usersource;

/**
 *
 */
class Twitter extends AbstractHandler
{
	/**
	 * Create a new instance of the adapter interface, using the usersource info
	 * for options etc.
	 *
	 * @return Orb\Auth\Adapter\AdapterInterface
	 */
	protected function _createAuthAdapterObject()
	{
		$classname = $this->getAuthAdapterClass();
		return new $classname(
			$this->_usersource['options']['consumer_key'],
			$this->_usersource['options']['consumer_secret']
		);
	}



	/**
	 * Get the classname of the auth adapter class.
	 *
	 * @return string
	 */
	public function getAuthAdapterClass()
	{
		return 'Orb\\Auth\\Adapter\\Twitter';
	}
}
