<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Usersource\Handler;

use \Application\DeskPRO\App;

use Application\DeskPRO\Entity\Usersource;

/**
 *
 */
class Twitter extends AbstractHandler
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
			'fields' => array(),
		);

		$keymap = array(
			'fullname' => 'full_name',
			'nickname' => 'nick_name',
		);

		foreach ($keymap as $findkey => $personkey) {
			if (isset($raw_userinfo[$findkey])) {
				$person_data['standard_fields'][$personkey] = $raw_userinfo[$findkey];
			}
		}

		$twitter_field_id = App::getSetting('core.twitter_field_id');
		if ($twitter_field_id) {
			$em = App::getOrm();
			$field = $em->find('DeskPRO:PersonField', $twitter_field_id);
			$value = $field->getHandler()->transformFormToStored($raw_userinfo['nickname']);

			$person_data['fields'][$twitter_field_id] = $value;
		}

		$website_field_id = App::getSetting('core.website_field_id');
		if ($website_field_id AND !empty($raw_userinfo['url'])) {
			$em = App::getOrm();
			$field = $em->find('DeskPRO:PersonField', $website_field_id);
			$value = $field->getHandler()->transformFormToStored($raw_userinfo['url']);

			$person_data['fields'][$website_field_id] = $value;
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
