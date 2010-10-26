<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter;

use \Orb\Auth\Adapter\SessionStateInterface;
use \Orb\Auth\Adapter\CallbackInterface;
use \Orb\Auth\StateHandler\StateHandlerInterface;
use \Orb\Auth\Result;

class Facebook extends AbstractCallbackAdatper
{
	protected $app_id;
	protected $app_secret;

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
			'cookie' => false,
		));
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$session = $this->fb->getSession();

		$me = false;
		if ($session) {
			try {
				$uid = $this->fb->getUser();
				$me = $this->fb->api('/me');
			} catch (\FacebookApiException $e) { }
		}

		if ($me) {
			return $this->_meToResult($me);
		}

		$redirect_url = $this->fb->getLoginUrl(array(
			'next' => $this->getCallbackUrl(),
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
		$session = $this->fb->getSession();

		$me = false;
		if ($session) {
			try {
				$uid = $this->fb->getUser();
				$me = $this->fb->api('/me');
			} catch (\FacebookApiException $e) { }
		}

		if ($me) {
			return new Result(Result::FAILURE, null, array('error_code' => 'failed_session', 'error_message' => 'No active FB session'));
		}

		return $this->_meToResult($me);
	}

	
	protected function _meToResult($me)
	{
		$identity = new \Orb\Auth\Identity($me['id'], $me);
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}
}