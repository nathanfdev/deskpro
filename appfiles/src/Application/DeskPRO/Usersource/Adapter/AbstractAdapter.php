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

abstract class AbstractAdapter implements CapabilityInformerInterface
{
	/**
	 * This adapter can render an actual signin form in HTML.
	 */
	const CAPABILITY_VIEW_FORM = 'form';

	/**
	 * This adapter can render a button.
	 */
	const CAPABILITY_VIEW_BUTTON = 'button';

	const VIEW_FORM = 'form';
	const VIEW_BUTTON = 'button';

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


	/**
	 * Render a view
	 *
	 * @param \Symfony\Component\Templating\EngineInterface $tpl
	 * @param string $type
	 * @return string
	 */
	abstract public function renderView(EngineInterface $tpl, $type, array $params = array());


	/**
	 * Create a new instance of the adapter interface, using the usersource info
	 * for options etc.
	 *
	 * @return \Orb\Auth\Adapter\AdapterInterface
	 */
	abstract protected function _createAuthAdapterObject();
}
