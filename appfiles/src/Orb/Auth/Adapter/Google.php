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

use \GoogleOpenID;

/**
 * Requirements:
 * - GoogleOpenID: http://andrewpeace.com/php-google-login-class.html
 */
class Google extends AbstractCallbackAdatper implements DisplayContextInterface
{
	protected $display = 'page';

	public function __construct()
	{

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
		$ah = GoogleOpenID::getAssociationHandle();
		$googleLogin = GoogleOpenID::createRequest($this->getCallbackUrl(), $ah, true);

		$params = $googleLogin->getArray();
		if ($this->display == 'popup') {
			$params['openid.ui.mode'] = 'popup';
		}

		$redirect_url = $googleLogin->endPoint() . '?' . http_build_query($params);

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
		$googleLogin = GoogleOpenID::getResponse();
		if($googleLogin->success()) {
			$user_id = $googleLogin->identity();
			$user_email = $googleLogin->email();
		}

		if ($user_id && $user_email) {

			$raw = array('user_id' => $user_id, 'user_email' => $user_email);
			foreach ($_GET as $k => $v) {
				if (strpos($k, 'openid_') === 0) {
					$raw[$k] = $v;
				}
			}

			$identity = new \Orb\Auth\Identity($user_id, $raw);
			$identity->setFriendlyIdentity($user_email);
			$result = new Result(Result::SUCCESS, $identity);

			return $result;
		}

		return new Result(Result::FAILURE, null, array('error_code' => 'failed_session', 'error_message' => 'No OpenID session'));
	}
}
