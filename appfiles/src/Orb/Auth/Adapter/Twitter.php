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

class Twitter extends AbstractCallbackAdatper
{
	protected $consumer_key;
	protected $consumer_secret;

	/**
	 * @param string $consumer_key     Your Twitter consumer key
	 * @param string $consumer_secret  Your Twitter consumer secret
	 */
	public function __construct($consumer_key, $consumer_secret)
	{
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_key;
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$oauth = $this->getOauthConsumer();
		$token = $oauth->getRequestToken();

		$state['orb_oauth_twitter_rtoken'] = $token;

		$redirect_url = $oauth->getRedirectUrl();

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
		$oauth = $this->getOauthConsumer();

		if (!isset($state['orb_oauth_twitter_rtoken'])) {
			return new Result(Result::FAILURE, null, array('error_code' => self::ERR_INVALID_TOKEN, 'error_message' => 'Invalid verify token'));
		}

		$access_token = $oauth->getAccessToken($callback_data, $state['orb_oauth_twitter_rtoken']);
		unset($state['orb_oauth_twitter_rtoken']);

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
		$identity->setFriendlyIdentity($access_token['screen_name']);
		
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}

	/**
	 * @return Zend_OAuth_Consumer
	 */
	public function getOauthConsumer()
	{
		return new Zend_OAuth_Consumer($this->getOauthConfig());
	}

	public function getOauthConfig()
	{
		return array(
			'callbackUrl' => $this->getCallbackUrl(),
			'siteUrl' => 'http://api.twitter.com/oauth',
			'consumerKey' => $this->consumer_key,
			'consumerSecret' => $this->consumer_secret,
			'requestScheme' => Zend_OAuth::REQUEST_SCHEME_HEADER,
			'signatureMethod' => 'HMAC-SHA1',
		);
	}

}