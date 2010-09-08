<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter;

use \Symfony\Component\HttpFoundation\Session;

/**
 * OrbRemoteLoginAuth is a simple protocol where the system redirects the user to a remote login
 * form. The remote service takes care of logging the user in, and then redirect the user back to
 * the system to finally handle the session. It is like OAuth except simpler.
 *
 * Note that this protocol does not care about security, it does not encrypt transmission of messages.
 * If you require security you should make sure the remote URL is HTTPS.
 *
 * STEP 1 - OUR SITE POST TO YOUR SITE:
 * The service requests a signing key that will be passed when the user is redirected to your site.
 * The following data is POST'ed to the initiate_url:
 * - orba_consumer_key: A secret key to authorize the server
 * - orba_user_key: A user string nonce specific for this request your server uses
 *                  to verify requsts from our server.
 *
 * The following data is expected back (JSON encoded):
 * - orba_token: A token we'll send back to identify this request
 * - orba_service_url: The URL to your sites login page.
 *
 * Your site must create and save a orba_token and it's corresponding orba_user_key.
 *
 * STEP 2 - USER REDIRECTED TO YOUR SITE:
 * The service will redirect the user to the orba_service_url with the following data in the query string.
 * - orba_token: The token we got back from your server
 * - orba_verify: sha1(orba_token . orba_user_key) to verify the request
 * - redirect_url: The URL we want your service to redirect back to.
 * This page on your site is where the user logs in.
 *
 * Your site must verify the request by comparing the hashed token, with the orba_user_key you
 * stored in the last step.
 *
 * Your site now stores a new orba_access_token with the logged in user.
 *
 * STEP 3 - YOUR SITE REDIRECTED TO OUR SITE:
 * After a user logs in, you must redirect them back to the redirect_url page with the following
 * data in the query string:
 * - orba_access_token: A unique token we'll use to authorize the next request
 * - orba_verify: sha1(orba_access_token . orba_user_key) we'll use to verify the request
 * If the user fails to log in, then the failed login should generate an error on your site. Only
 * redirect the user back to our site with a successful login.
 *
 * STEP 4 - OUR SITE POST TO YOUR SITE:
 * The service will now verity the users identity, and may optionally request userinfo. The data posted:
 * - orba_access_token: The access token you gave us to verify this request
 * - orba_verify: sha1(orba_access_token . orba_user_key)
 * - orba_with_userinfo: 1 if we also want userinfo back
 *
 * The following data is returned on success (JSON encoded):
 * - is_success: 1
 * - identity: Any unique identifier for this user (user ID for example)
 * - userinfo: (object of user data, only set if orba_with_userinfo was 1)
 *
 * `userinfo` if possible should have keys named such (standard naming for standard fields) so generic components
 * can use your service with no interaction:
 * - orba_identity: A unique identity of this user on your system such as numeric ID
 * - orba_username: A preferred username, or an array of preferred usernames in order of priority
 * - orba_email: A preferred email address, or an array of preferred email addresses in order of priority
 * - orba_name: The users real name
 */
class OrbRemoteLoginAuth implements AdapterInterface
{
	/**
	 * The key to authenticate the request
	 * @var string
	 */
	protected $consumer_key = null;

	/**
	 * The URL to call to initiate the process
	 * @var string
	 */
	protected $initiate_url = null;

	/**
	 * The URL to redirect the user back to upon successful login
	 * @var string
	 */
	protected $redirect_url = null;

	/**
	 * The URL we'll use to verify a users login and possibly fetch userinfo
	 * @var string
	 */
	protected $verify_url = null;

	/**
	 * The session we'll use to store various keys.
	 * @var \Symfony\Component\HttpFoundation\Session
	 */
	protected $session;

	/**
	 * HTTP client
	 * @var \Zend\Http\Client
	 */
	protected $http;

	/**
	 * Do we request userinfo as well?
	 * @var int
	 */
	protected $with_userinfo = 1;

	/**
	 * Data we got back when user returned from providers website
	 * @var array
	 */
	protected $got_data = array();

	/**
	 * @param Session $session
	 * @param string $consumer_key
	 * @param string $initiate_url The remote URL we'll call to initiate the process
	 * @param string $redirect_url The local URL to redirect the user BACK to upon login
	 * @param string $verify_url   The URL to call
	 */
	public function __construct(Session $session, $consumer_key, $initiate_url, $redirect_url, $verify_url)
	{
		$this->session      = $session;
		$this->consumer_key = $consumer_key;
		$this->initiate_url = $initiate_url;
		$this->redirect_url = $redirect_url;
		$this->verify_url   = $verify_url;
	}

	

	/**
	 * Set the orba_access_token and orba_verify we got back from a user redirected back to our site.
	 *
	 * @param string $access_token
	 * @param string $verify_key
	 */
	public function setKeys($access_token, $verify_key)
	{
		$this->got_data = array(
			'orba_access_token' => $access_token,
			'orba_verify' => $verify_key
		);
	}



	/**
	 * Set the keys from an array
	 * 
	 * @param array $get The array to get from (ie $_GET)
	 */
	public function setKeysFromArray(array $get)
	{
		$this->got_data = $get;
	}


	
	/**
	 * Set if we want userinfo or not
	 *
	 * @param bool $yes_or_no
	 */
	public function setWithUserinfo($yes_or_no)
	{
		$this->with_userinfo = (int)((bool)$yes_or_no);
	}



	/**
	 * Authenticate a user.
	 *
	 * @return
	 */
	public function authenticate()
	{
		// If we dont have tokens yet, we must initiate the request
		if (!isset($this->got_data['orba_access_token']) OR !isset($got_tokens['orba_verify']) OR !$this->session->has('orba_user_key')) {
			return $this->_initiate();
		}

		#------------------------------
		# Verify the callback
		#------------------------------

		$check_verify = sha1($this->got_data['orba_access_token'] . $this->session->get('orba_user_key'));

		if ($check_verify != $got_tokens['orba_verify']) {
			return new \Orb\Auth\Result(\Orb\Auth\Result::FAILURE, null, array('error_code' => -10, 'error_message' => 'Invalid verify token'));
		}

		#------------------------------
		# Now fetch the data
		#------------------------------

		$http = $this->getHttpClient();
		$http->resetParameters();

		$http->setUri($this->verify_url);
		$http->setParameterPost('orba_access_token', $this->got_data['orba_access_token']);
		$http->setParameterPost('orba_verify', sha1($service_data['orba_access_token'] . $this->session->get('orba_user_key')));
		if ($this->with_userinfo) {
			$http->setParameterPost('orba_with_userinfo', 1);
		}

		$http_result = $http->request(\Zend\Http\Client::POST);

		$data = @json_decode($http_result->getBody(), true);
		if (!$userdata) {
			throw \UnexpectedValueException('Invalid JSON returned from service');
		}

		if (isset($data['is_error'])) {
			return new \Orb\Auth\Result(\Orb\Auth\Result::FAILURE, null, $data);
		}

		$identity = new \Orb\Auth\Identity($data['identity'], isset($userdata['userinfo']) ? $userdata : array());
		$result = new \Orb\Auth\Result(\Orb\Auth\Result::SUCCESS, $identity);

		return $result;
	}

	protected function _initiate()
	{
		// The user key used in various signings
		$user_key = \Orb\Util\Strings::random(20, \Orb\Util\Strings::CHARS_ALPHANUM_IU);

		#------------------------------
		# Initiate the request on the service
		#------------------------------

		$http = $this->getHttpClient();
		$http->resetParameters();
		$http->setUri($this->initiate_url);

		$http->setParameterPost('orba_consumer_key', $this->consumer_key);
		$http->setParameterPost('orba_user_key', $user_key);

		$http_result = $http->request(\Zend\Http\Client::POST);

		$service_data = @json_decode($http_result->getBody(), true);
		if (!$service_data) {
			throw \UnexpectedValueException('Invalid JSON returned from service');
		}

		#------------------------------
		# Now store the tokens in the session and
		# redirect the user
		#------------------------------

		$this->session->set('orba_user_key', $user_key);

		$redirect_url = $service_data['orba_service_url'];
		if (\strpos($redirect_url, '?') === false) {
			$redirect_url .= '?';
		} else {
			$redirect_url .= '&';
		}
		$redirect_url .= 'orba_token=' . urlencode($service_data['orba_token']);
		$redirect_url .= '&orba_verify=' . sha1($service_data['orba_token'] . $user_key);
		$redirect_url .= '&redirect_url=' . urlencode($this->redirect_url);

		$result = new \Orb\Auth\Result(\Orb\Auth\Result::REQUIRES_REDIRECT, null, array('redirect_url' => $redirect_url));
		return $result;
	}



	/**
	 * Set a custom HTTP client. If one is not set, a default client will be used automatically.
	 *
	 * @param \Zend\Http\Client $http
	 */
	public function setHttpClient(\Zend\Http\Client $http)
	{
		$this->http = $http;
	}


	/**
	 * Get the HTTP client.
	 *
	 * @return \Zend\Http\Client
	 */
	public function getHttpClient()
	{
		if ($this->http !== null) return $this->http;

		$this->http = new \Zend\Http\Client();

		return $this->http;
	}
}