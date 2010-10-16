<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chroder
 * Date: 10-10-15
 * Time: 7:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DeskPRO\Usersource\Handler;

use Application\CoreBundle\Entity\Usersource;

abstract class AbstractHandler
{
	/**
	 * @var Orb\Auth\Adapter\AdapterInterface
	 */
	protected $_auth_adapter = null;

	/**
	 * @var Application\CoreBundle\Entity\Usersource
	 */
	protected $_usersource = null;


	/**
	 * Create a new handler based off the usersource.
	 *
	 * @param Application\CoreBundle\Entity\Usersource $usersource
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
