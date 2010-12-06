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

use \DeskPRO\App;

use Application\CoreBundle\Entity\Usersource;

/**
 * Facebook handler
 */
class Facebook extends AbstractHandler
{
	/**
	 * Get an array of person data mapped to raw userinfo we got back from a twitter
	 * login.
	 *
	 * @param array $raw_userinfo
	 * @return array
	 */
	public function getPersonData(array $raw_userinfo)
	{
		$person_data = array(
			'standard_fields' => array(),
			'emails' => array(),
		);

		if (!empty($raw_userinfo['name'])) {
			$person_data['full_name'] = $raw_userinfo['name'];
		}

		if (!empty($raw_userinfo['email'])) {
			$person_data['emails'][] = $raw_userinfo['email'];
		}

		return $person_data;
	}

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
			$this->_usersource['options']['app_id'],
			$this->_usersource['options']['app_secret']
		);
	}



	/**
	 * Get the classname of the auth adapter class.
	 *
	 * @return string
	 */
	public function getAuthAdapterClass()
	{
		return 'Orb\\Auth\\Adapter\\Facebook';
	}
}
