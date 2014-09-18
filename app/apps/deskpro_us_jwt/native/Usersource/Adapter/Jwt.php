<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace deskpro_us_jwt\Usersource\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Usersource\Adapter\AbstractAdapter;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;

class Jwt extends AbstractAdapter
{
	public function getFieldsFromIdentity(Identity $identity)
	{
		$info = $identity->getRawData();
		return array(
			'name'             => isset($info['name']) ? $info['name'] : '',
			'first_name'       => isset($info['first_name']) ? $info['first_name'] : '',
			'last_name'        => isset($info['last_name']) ? $info['last_name'] : '',
			'email'            => isset($info['email']) ? $info['email'] : '',
			'email_confirmed'  => true,
		);
	}

	/**
	 * @return \deskpro_magento\Usersource\Auth\Magento
	 */
	protected function _createAuthAdapterObject()
	{
		$options = $this->usersource->options;

		return new \deskpro_us_jwt\Usersource\Auth\Jwt($options);
	}

	public function getAgentLogoutRedirectUrl()
	{
		return isset($this->usersource->options['logout_agent_url']) ? $this->usersource->options['logout_agent_url'] : '';
	}

	public function getUserLogoutRedirectUrl()
	{
		return isset($this->usersource->options['logout_user_url']) ? $this->usersource->options['logout_user_url'] : '';
	}

	/**
	 * @return array
	 */
	public function getCapabilities()
	{
		$capabilities = array(
			UsersourceInfo::CAPABILITY_SSO,
			UsersourceInfo::CAPABILITY_SSO_JS
		);

		if ($custom_button_text = $this->usersource->options['login_custom_text']) {
			$capabilities[] = UsersourceInfo::CAPABILITY_LOGIN_BTN;
			$capabilities[] = UsersourceInfo::CAPABILITY_WIDGET_OVERLAY_BTN;
		}

		return $capabilities;
	}
}
