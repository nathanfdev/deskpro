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

namespace Application\DeskPRO\Auth;


use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;

/**
 * AuthenticationManager ties the pieces of the Authentication system together and makes some executive decisions about
 * the authentication system during the flow of a request for a particular interface.
 *
 * The AuthenticationManager has the right answers for the CURRENT INTERFACE, freeing you from having to worry about which
 * interface we are on.
 *
 * @package Application\DeskPRO\Auth
 */
class AuthenticationManager
{
	/**
	 * The interface this request was made from. Relevant for various auth settings.
	 *
	 * @var string
	 */
	private $interface;

	/**
	 * @var UsersourceManager
	 */
	private $usersourceManager;

	/**
	 * The usersources relevant for this request
	 *
	 * @var \Application\DeskPRO\Usersource\UsersourceCollection collection of usersources for this interface
	 */
	private $usersourcesForInterface;

	/**
	 * @var AuthSettings
	 */
	private $authSettings;

	/**
	 * The interface settings relevant for this request
	 *
	 * @var \Application\DeskPRO\Auth\AuthInterfaceSettings
	 */
	private $settings;


	/**
	 * @param UsersourceManager $usersourceManager system service
	 * @param AuthSettings      $authSettings system service
	 * @param string            $interface this MUST be "user" or "agent"
	 */
	public function __construct(AuthSettings $authSettings, UsersourceManager $usersourceManager, $interface)
	{
		$this->usersourceManager = $usersourceManager;
		$this->authSettings = $authSettings;
		$this->interface    = $interface;

		$this->usersourcesForInterface = $this->usersourceManager->getAll()->forInterface($interface);
		$this->settings = $interface === 'user' ? $authSettings->getUserInterfaceSettings() : $authSettings->getAgentInterfaceSettings();
	}


	/**
	 * @return AuthInterfaceSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}


	/**
	 * @return UsersourceManager
	 */
	public function getUsersourceManager()
	{
		return $this->usersourceManager;
	}


	/**
	 * All usersources for this interface
	 *
	 * @return \Application\DeskPRO\Usersource\UsersourceCollection
	 */
	public function getUsersources()
	{
		return $this->usersourcesForInterface;
	}

	/**
	 * @return bool
	 */
	public function hasFormLoginCapability()
	{
		return count($this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN)) > 0;
	}


	/**
	 * Usersources that have a button to display to login
	 *
	 * @return \Application\DeskPRO\Usersource\UsersourceCollection
	 */
	public function getLoginButtonUsersources()
	{
		return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_LOGIN_BTN);
	}


	/**
	 * @return bool
	 */
	public function hasLoginButtonUsersources()
	{
		return count($this->getLoginButtonUsersources()) > 0;
	}
}
 