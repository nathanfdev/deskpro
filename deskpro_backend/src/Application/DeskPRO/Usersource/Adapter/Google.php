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

use Orb\Util\CapabilityInformerInterface;
use Orb\Auth\Identity;

class Google extends AbstractAdapter
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		$info = $identity->getRawData();
		return array(
			'email'            => $info['user_email'],
			'email_confirmed'  => true,
		);
	}


	/**
	 * @return \Orb\Auth\Adapter\Twitter
	 */
	protected function _createAuthAdapterObject()
	{
		return new \Orb\Auth\Adapter\Google();
	}


	/**
	 * @return array
	 */
	public function getCapabilities()
	{
		return array(
			'tpl_login_pull_btn',
			'tpl_widget_overlay_btn'
		);
	}


	/**
	 * @param  mixed $capability
	 * @return bool
	 */
	public function isCapable($capability)
	{
		return in_array($capability, $this->getCapabilities());
	}
}
