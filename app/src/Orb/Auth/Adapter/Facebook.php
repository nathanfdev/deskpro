<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth\Adapter;

use \Orb\Auth\Adapter\SessionStateInterface;
use \Orb\Auth\Adapter\CallbackInterface;
use \Orb\Auth\StateHandler\StateHandlerInterface;
use \Orb\Auth\Result;

/**
 * Requirements:
 * - Facebook SDK: https://github.com/facebook/php-sdk
 */
class Facebook extends AbstractCallbackAdatper implements DisplayContextInterface
{
	protected $app_id;
	protected $app_secret;
	protected $display = 'page';

	/**
	 * The facebook object
	 * @var Facebook
	 */
	protected $fb;

	/**
	 * @param string $app_id      Your Facebook app id
	 * @param string $app_secret  Your facebook app secret
	 */
	public function __construct($app_id, $app_secret)
	{
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;

		$this->fb = new \Facebook(array(
			'appId'  => $this->app_id,
			'secret' => $this->app_secret,
		));
	}


	/**
	 * Sets the display context: page or popup
	 *
	 * @param $context
	 * @throws \InvalidArgumentException
	 */
	public function setDisplayContext($context)
	{
		$context = strtolower($context);
		if (!in_array($context, array('page', 'popup'))) {
			throw new \InvalidArgumentException("Invalid display context `$context`");
		}

		$this->display = $context;
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		// Gets a userid or false if no user logged in
		$user = $this->fb->getUser();

		$me = false;
		if ($user) {
			try {
				$me = $this->fb->api('/me');
			} catch (\FacebookApiException $e) { }
		}

		// Already a user
		if ($me) {
			return $this->_meToResult($me);
		}

		$redirect_url = $this->fb->getLoginUrl(array(
			'redirect_uri' => $this->getCallbackUrl(),
			'display' => $this->display,
			'req_perms' => 'user_about_me,user_birthday,user_website,email',
		));
		$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $redirect_url));
		return $result;
	}



	/**
	 * Process the callback and return a final result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		$session = $this->fb->getUser();

		$me = false;
		if ($session) {
			try {
				$me = $this->fb->api('/me');
			} catch (\FacebookApiException $e) { }
		}

		if (!$me) {
			return new Result(Result::FAILURE, null, array('error_code' => 'failed_session', 'error_message' => 'No active FB session'));
		}

		return $this->_meToResult($me);
	}


	protected function _meToResult($me)
	{
		$identity = new \Orb\Auth\Identity($me['id'], $me);
		$identity->setFriendlyIdentity($identity['link']);
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}
}
