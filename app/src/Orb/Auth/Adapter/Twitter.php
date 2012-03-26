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
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Adapter\SessionStateInterface;
use Orb\Auth\Adapter\CallbackInterface;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Auth\Result;

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
		$this->consumer_secret = $consumer_secret;
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$oauth = $this->getOauthConsumer();

		try {
			$token = $oauth->getRequestToken();
		} catch (\Zend\Oauth\Exception $e) {
			$result = new Result(Result::FAILURE_EXCEPTION, null, array(Result::MSG_EXCEPTION => $e));
			return $result;
		}

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
			return new Result(Result::FAILURE, null, array('error_code' => 'invalid_token', 'error_message' => 'Invalid verify token'));
		}

		$access_token = $oauth->getAccessToken($callback_data, $state['orb_oauth_twitter_rtoken']);
		unset($state['orb_oauth_twitter_rtoken']);

		$client = $access_token->getHttpClient($this->getOauthConfig());
		$client->setUri('http://api.twitter.com/account/verify_credentials.json');
		$client->setMethod(\Zend\Http\Client::GET);
		$response = $client->request();

		$account_data = @json_decode($response->getBody(), true);

		if (!$account_data OR !isset($account_data['id'])) {
			return new Result(Result::FAILURE, null, array('error_code' => 'failed_verify_credentials', 'error_message' => 'Failed to call API service to verify credentials'));
		}

		$raw_userinfo = array(
			'access_token' => $access_token->getToken(),
			'access_token_secret' => $access_token->getTokenSecret(),
			'identity' => $account_data['id'],
			'identity_friendly' => $account_data['screen_name'],
			'fullname' => $account_data['name'],
			'url' => $account_data['url'],
			'nickname' => $account_data['screen_name'],
			'raw' => $account_data,
		);

		$identity = new \Orb\Auth\Identity($account_data['id'], $raw_userinfo);
		$identity->setFriendlyIdentity($account_data['screen_name']);

		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}

	/**
	 * @return \Zend\OAuth\Consumer
	 */
	public function getOauthConsumer()
	{
		return new \Zend\Oauth\Consumer($this->getOauthConfig());
	}

	public function getOauthConfig()
	{
		return array(
			'callbackUrl' => $this->getCallbackUrl(),
			'siteUrl' => 'http://api.twitter.com/oauth',
			'consumerKey' => $this->consumer_key,
			'consumerSecret' => $this->consumer_secret,
		);
	}

}
