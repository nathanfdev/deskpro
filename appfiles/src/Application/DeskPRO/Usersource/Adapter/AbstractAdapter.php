<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Usersource
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Entity\Usersource;
use Symfony\Component\Templating\EngineInterface;

use Orb\Util\CapabilityInformerInterface;
use Orb\Auth\Identity;
use Orb\Util\Util;

abstract class AbstractAdapter implements CapabilityInformerInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Usersource
	 */
	protected $usersource;

	/**
	 * @var \Orb\Auth\Adapter\AdapterInterface
	 */
	protected $_auth_adapter;

	final public function __construct(Usersource $usersource)
	{
		$this->usersource = $usersource;
		$this->init();
	}

	protected function init()
	{

	}


	/**
	 * Given an identity returned from an auth adapter, get the mapped fields that we can apply
	 * to a Person record. For example, email addresses or names.
	 *
	 * @param \Orb\Auth\Identity $identity
	 * @return array
	 */
	public function getFieldsFromIdentity(Identity $identity)
	{
		return array();
	}


	/**
	 * Get the adapter.
	 *
	 * @return \Orb\Auth\Adapter\AdapterInterface
	 */
	public function getAuthAdapter()
	{
		if ($this->_auth_adapter !== null) {
			return $this->_auth_adapter;
		}

		$this->_auth_adapter = $this->_createAuthAdapterObject();

		return $this->_auth_adapter;
	}


	public function applyResultToUser()
	{

	}


	/**
	 * Create a new instance of the adapter interface, using the usersource info
	 * for options etc.
	 *
	 * @return \Orb\Auth\Adapter\AdapterInterface
	 */
	abstract protected function _createAuthAdapterObject();

	public function getTypename()
	{
		return strtolower(Util::getBaseClassname($this));
	}
}
