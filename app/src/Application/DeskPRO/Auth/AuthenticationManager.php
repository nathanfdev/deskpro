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


use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Identity;
use Orb\Auth\Result;

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
	 * @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory
	 */
	private $authAdapterFactory;


	/**
	 * @param UsersourceManager            $usersourceManager system service
	 * @param AuthSettings                 $authSettings      system service
	 * @param UsersourceAuthAdapterFactory $auth_adapter_factory
	 * @param string                       $interface         this MUST be "user" or "agent"
	 */
	public function __construct(
		AuthSettings $authSettings,
		UsersourceManager $usersourceManager,
		UsersourceAuthAdapterFactory $auth_adapter_factory,
		$interface
	) {
		$this->usersourceManager  = $usersourceManager;
		$this->authSettings       = $authSettings;
		$this->authAdapterFactory = $auth_adapter_factory;
		$this->interface          = $interface;

		$this->usersourcesForInterface = $this->usersourceManager->getAll()->forInterface($interface);
		$this->settings = $interface === 'user' ? $authSettings->getUserInterfaceSettings() : $authSettings->getAgentInterfaceSettings();
	}


	/**
	 * Settings relevant to THIS request (interface aware)
	 *
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
	 * Can we handle form logins?
	 *
	 * @return bool
	 */
	public function hasFormLoginCapability()
	{
		return count($this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN)) > 0;
	}


	/**
	 * Loop the usersources (in order) and try to authenticate the user. First yes wins.
	 *
	 * @param $identifier
	 * @param $password
	 * @return Result
	 */
	public function authenticateFormLogin($identifier, $password)
	{
		#------------------------------
		# Auth local
		#------------------------------

		// local deskpro auth is now a usersource (in the db) like all others
		//if ($this->container->getSetting('core.deskpro_source_enabled') || DP_INTERFACE != 'user') {
		//	$adapter = new \Application\DeskPRO\Auth\Adapter\Local(App::getOrm());
		//	$adapter->setCredentials($this->in->getString('email'), $this->in->getString('password'));
		//	$result = $adapter->authenticate();
		//
		//	if ($result->isValid()) {
		//		return $result;
		//	}
		//}

		#------------------------------
		# Auth usersources that accept local input
		#------------------------------

		$usersources = $this->getFormLoginUsersources();
		foreach ($usersources as $us) {
			$adapter = $this->authAdapterFactory->getAuthAdapter($us);

			if ($adapter instanceof FormLoginInterface) {
				$adapter->setFormData(
					array(
						'username' => $identifier,
						'password' => $password
					)
				);

				try {
					$result = $adapter->authenticate();
				} catch (\Exception $e) {
					KernelErrorHandler::logException($e, false);
					$GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'] = $adapter;
					$GLOBALS['DP_AUTH_EXCEPTION']         = $e;
					continue;
				}

				if ($result->isValid()) {
					$login_processor = new LoginProcessor($us, $result->getIdentity());
					$person          = $login_processor->getPerson();

					$identity = new Identity($person->id, array('person' => $person));
					$result   = new Result(Result::SUCCESS, $identity);

					return $result;
				}
			}
		}

		return new Result(Result::FAILURE_INVALID_CREDS);
	}


	/**
	 * @return \Application\DeskPRO\Usersource\UsersourceCollection
	 */
	public function getFormLoginUsersources()
	{
		return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN);
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
	 * Are we displaying any extra login buttons/icons?
	 *
	 * @return bool
	 */
	public function hasLoginButtonUsersources()
	{
		return count($this->getLoginButtonUsersources()) > 0;
	}
}
 