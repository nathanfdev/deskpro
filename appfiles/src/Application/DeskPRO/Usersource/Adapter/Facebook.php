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

class Facebook extends AbstractAdapter
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		$info = $identity->getRawData();
		return array(
			'name'             => isset($info['name']) ? $info['name'] : '',
			'first_name'       => isset($info['first_name']) ? $info['first_name'] : '',
			'last_name'        => isset($info['last_name']) ? $info['last_name'] : '',
			'email'            => isset($info['email']) ? $info['email'] : '',
			'email_confirmed'  => isset($info['verified']) ? $info['verified'] : '',
		);
	}

	/**
	 * @param \Symfony\Component\Templating\EngineInterface $tpl
	 * @param string $type
	 * @return string
	 */
	public function renderView(EngineInterface $tpl, $type, array $params = array())
	{
		$params['usersource'] = $this->usersource;

		switch ($type) {
			case self::VIEW_BUTTON:
				return $tpl->render('DeskPRO:Auth:facebook-btn.html.twig', $params);
				break;
		}
	}


	/**
	 * @return \Orb\Auth\Adapter\Twitter
	 */
	protected function _createAuthAdapterObject()
	{
		return new \Orb\Auth\Adapter\Facebook(
			$this->usersource->getOption('app_key'),
			$this->usersource->getOption('app_secret')
		);
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
