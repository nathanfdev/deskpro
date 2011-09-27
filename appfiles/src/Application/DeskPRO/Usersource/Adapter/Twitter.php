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

class Twitter extends AbstractAdapter
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		$info = $identity->getRawData();
		return array(
			'name' => $info['fullname'],

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
				return $tpl->render('DeskPRO:Auth:twitter-btn.html.twig', $params);
				break;
		}
	}


	/**
	 * @return \Orb\Auth\Adapter\Twitter
	 */
	protected function _createAuthAdapterObject()
	{
		return new \Orb\Auth\Adapter\Twitter(
			$this->usersource->getOption('consumer_key'),
			$this->usersource->getOption('consumer_secret')
		);
	}


	/**
	 * @return array
	 */
	public function getCapabilities()
	{
		return array(
			self::CAPABILITY_VIEW_BUTTON
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
