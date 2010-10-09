<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter;

use \Orb\Auth\Result;
use \Symfony\Component\HttpFoundation\Session;

/**
 * Login with twitter
 */
class Twitter implements AdapterInterface
{
	protected $consumer_key;
	protected $consumer_secret;
	protected $callback_url;

	/**
	 * The session we'll use to store various keys.
	 * @var \Symfony\Component\HttpFoundation\Session
	 */
	protected $session;

	/**
	 * Data that came with the request
	 * @var array
	 */
	protected $got_data = array();

	protected $is_callback = false;

	/**
	 * @param Session $session         Session we'll use to store various keys
	 * @param string $consumer_key     Your Twitter consumer key
	 * @param string $consumer_secret  Your Twitter consumer secret
	 * @param string $callback_url     The URL that the user returns to to finish the OAuth login
	 */
	public function __construct(Session $session, $consumer_key, $consumer_secret, $callback_url)
	{
		$this->session = $session;
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_key;
		$this->callback_url = $callback_url;
	}


	
	/**
	 * Switch handling to callback.
	 * 
	 * @param array $got_data
	 */
	public function setCallbackMode(array $got_data)
	{
		$this->is_callback = true;
		$this->got_data = $got_data;
	}


	public function authenticate()
	{
		if ($this->is_callback) {
			return $this->authenticateCallback();
		}

		$oauth = $this->getOauthConsumer();
		$token = $oauth->getRequestToken();

		$this->session->set('orb_oauth_twitter_rtoken', $token);

		$redirect_url = $oauth->getRedirectUrl();

		$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $redirect_url));
		return $result;
	}

	public function authenticateCallback()
	{
		$oauth = $this->getOauthConsumer();

		if (!$this->session->has('orb_oauth_twitter_rtoken')) {
			return new Result(Result::FAILURE, null, array('error_code' => self::ERR_INVALID_TOKEN, 'error_message' => 'Invalid verify token'));
		}

		$access_token = $oauth->getAccessToken($this->got_data, $this->session->get('orb_oauth_twitter_rtoken'));
		$this->session->remove('orb_oauth_twitter_rtoken');

		$client = $access_token->getHttpClient($this->getOauthConfig());
		$client->setUri('http://api.twitter.com/version/account/verify_credentials.json');
		$client->setMethod(Zend_Http_Client::POST);
		$response = $client->request();

		$account_data = json_decode($response->getBody(), true);

		$raw_userinfo = array(
			'access_token' => $access_token->getToken(),
			'access_token_secret' => $access_token->getTokenSecret(),
			'identity' => $account_data['id'],
			'identity_friendly' => $access_token['screen_name'],
			'fullname' => $account_data['name'],
			'url' => $account_data['url'],
			'nickname' => $access_token['screen_name'],
			'raw' => $account_data,
		);

		$identity = new \Orb\Auth\Identity($account_data['id'], $raw_userinfo);
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}

	
	
	/**
	 * @return Zend\OAuth\Consumer
	 */
	public function getOauthConsumer()
	{
		return new \Zend\OAuth\Consumer($this->getOauthConfig());
	}

	public function getOauthConfig()
	{
		return array(
			'callbackUrl' => $this->callback_url,
			'siteUrl' => 'http://api.twitter.com/oauth',
			'consumerKey' => $this->consumer_key,
			'consumerSecret' => $this->consumer_secret,
			'requestScheme' => \Zend\OAuth\OAuth::REQUEST_SCHEME_HEADER,
			'signatureMethod' => 'HMAC-SHA1',
		);
	}
}