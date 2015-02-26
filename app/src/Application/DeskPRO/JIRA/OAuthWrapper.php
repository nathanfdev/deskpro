<?php
/**
 * https://bitbucket.org/atlassian_tutorial/atlassian-oauth-examples/src/d625161454d1ca97b4515c6147b093fac9a68f7e/php/?at=default
 */


namespace Application\DeskPRO\JIRA;

use Application\DeskPRO\Service\JIRA;
use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;

class OAuthWrapper
{
	protected $base_url;
	protected $private_key;
	protected $callback_url;

	protected $tokens;
	protected $consumer_key;
	protected $consumer_secret = '';

	protected $request_token_url = '/plugins/servlet/oauth/request-token';
	protected $access_tocken_url = '/plugins/servlet/oauth/access-token';
	protected $authorization_url = '/plugins/servlet/oauth/authorize?oauth_token=%s';

	protected $client;
	protected $service;

	public function __construct(JIRA $service, $callbackUrl = null)
	{
		$this->service = $service;

		if (!$this->base_url = $this->service->getUrl()) {
			throw new \Exception('JIRA base url is required', 1000);
		}

		if (!$this->private_key = $this->service->getPrivateKey()) {
			throw new \Exception('JIRA private key is required', 1001);
		}

		if (!$this->consumer_key = $this->service->getConsumerKey()) {
			throw new \Exception('JIRA consumer key is required', 1002);
		}


		$this->tokens = $this->service->getTokens();
		$this->callback_url = $callbackUrl;
	}

	/**
	 * @return array
	 */
	public function requestTempCredentials()
	{
		if (!empty($this->tokens['oauth_token'])) {
			$this->tokens = array();
		}

		return $this->requestCredentials(
			$this->base_url . $this->request_token_url . '?oauth_callback=' . $this->callback_url
		);
	}

	/**
	 * @param $token
	 * @param $tokenSecret
	 * @param $verifier
	 * @return array
	 * @throws Exception
	 * @throws \Exception
	 */
	public function requestAuthCredentials($token, $tokenSecret, $verifier)
	{
		$this->service->setTokens(array());

		$credentials = $this->requestCredentials(
			$this->base_url . $this->access_tocken_url . '?oauth_callback=' . $this->callback_url . '&oauth_verifier=' . $verifier,
			$token,
			$tokenSecret
		);

		$this->tokens = $credentials;
		$this->service->setTokens($credentials);
		return $credentials;
	}

	/**
	 * @param $url
	 * @param bool $token
	 * @param bool $tokenSecret
	 * @return array
	 * @throws Exception
	 * @throws \Exception
	 */
	protected function requestCredentials($url, $token = false, $tokenSecret = false)
	{
		$client = $this->getClient($token, $tokenSecret);
		try {
			$response = $client->post($url)->send();
		} catch(\Exception $e) {
			// todo handle curl errors
			throw $e;
		}

		$body = (string) $response->getBody();

		$tokens = array();
		parse_str($body, $tokens);

		if (empty($tokens)) {
			throw new \Exception(sprintf(
				'Bad response from host. Expected urlencoded string but "%s" received.', substr($body, 0, 200)
			), 1003);
		}

		if (!isset($tokens['oauth_token'])) {
			throw new \Exception(
				'Bad response from host. No OAuth token provided.'
			, 1004);
		}

		return $this->tokens = $tokens;
	}

	/**
	 * @param null $token
	 * @param null $tokenSecret
	 * @return Client
	 * @throws \Exception
	 */
	public function getClient($token = null, $tokenSecret = null)
	{
		if (!$token && $this->client) {
			return $this->client;
		}

		$token = $token ?: (isset($this->tokens['oauth_token']) ? $this->tokens['oauth_token'] : null);
		$secret = $tokenSecret ?: (isset($this->tokens['oauth_token_secret']) ? $this->tokens['oauth_token_secret'] : null);

		$this->client = new Client($this->base_url);
		$privateKey = $this->private_key;

		$plugin = new OauthPlugin(array(
			'consumer_key' 		=> $this->consumer_key,
			'consumer_secret' 	=> $this->consumer_secret,
			'token' 			=> $token,
			'token_secret' 		=> $secret,
			'signature_method' => 'RSA-SHA1',
			'signature_callback' => function($stringToSign, $key) use ($privateKey) {

				$certificate = openssl_pkey_get_private($privateKey);
				$privateKeyId = openssl_get_privatekey($certificate);
				$signature = null;
				if (!@openssl_sign($stringToSign, $signature, $privateKeyId)) {
					throw new \Exception('Invalid Private Key', 1004);
				}
				@openssl_free_key($privateKeyId);
				return $signature;
			}
		));

		$this->client->addSubscriber($plugin);
		return $this->client;
	}

	/**
	 * @return string
	 */
	public function getAuthUrl()
	{
		return $this->base_url . sprintf($this->authorization_url, urlencode($this->tokens['oauth_token']));
	}
}
