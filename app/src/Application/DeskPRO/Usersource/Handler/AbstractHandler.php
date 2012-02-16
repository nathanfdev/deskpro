<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Usersource\Handler;

use Application\DeskPRO\Entity\Usersource;

/**
 * A handler takes a usersource and knows how to facilitate create an adapter.
 */
abstract class AbstractHandler
{
	/**
	 * @var Orb\Auth\Adapter\AdapterInterface
	 */
	protected $_auth_adapter = null;

	/**
	 * @var Application\DeskPRO\Entity\Usersource
	 */
	protected $_usersource = null;


	/**
	 * Create a new handler based off the usersource.
	 *
	 * @param Application\DeskPRO\Entity\Usersource $usersource
	 */
	public function __construct(Usersource $usersource)
	{
		$this->_usersource = $usersource;
		$this->init();
	}



	/**
	 * Empty hook method for sub-classes to implement any initialization code.
	 * @return void
	 */
	protected function init()
	{

	}


	/**
	 * Gets an array of data we'll use to apply to a person. This basically
	 * normalizes a raw userinfo data from the adapter into standard array we can use.
	 *
	 * Note that some usersources might not have any useful info, but this is used
	 * anyway.
	 *
	 * @param array $raw_userinfo
	 */
	public function getPersonData(array $raw_userinfo)
	{
		return \Application\DeskPRO\Util::getPersonData($raw_userinfo);
	}


	/**
	 * Get the adapter.
	 *
	 * @return Orb\Auth\Adapter\AdapterInterface
	 */
	public function getAuthAdapter()
	{
		if ($this->_auth_adapter !== null) {
			return $this->_auth_adapter;
		}

		$this->_auth_adapter = $this->_createAuthAdapterObject();

		return $this->_auth_adapter;
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
		return new $classname();
	}



	/**
	 * Get the classname of the auth adapter class.
	 *
	 * @return string
	 */
	abstract public function getAuthAdapterClass();
}
