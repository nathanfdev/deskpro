<?php
/**
 * https://bitbucket.org/atlassian_tutorial/atlassian-oauth-examples/src/d625161454d1ca97b4515c6147b093fac9a68f7e/php/?at=default
 */


namespace Application\DeskPRO\JIRA;

use Application\DeskPRO\Settings\Settings;
use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;

class OAuthWrapper
{
	const PARAM_URL        = 'jira.base_url';
	const PARAM_CONSUMER   = 'jira.consumer_key';
	const PARAM_KEY        = 'core_jira.private_key';
	const PARAM_TOKENS      = 'jira.oauth_tokens';

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
	protected $settings;

	public function __construct(Settings $settings, $callbackUrl = null)
	{
		$this->settings = $settings;

		if (!$this->base_url = $this->settings->get(self::PARAM_URL)) {
			throw new \Exception('JIRA base url is required');
		}

		if (!$this->private_key = $this->settings->get(self::PARAM_KEY)) {
			throw new \Exception('JIRA private key is required');
		}


		if ($tokens = $this->settings->get(self::PARAM_TOKENS)) {
			$this->tokens = unserialize($tokens);
		}

		$this->callback_url = $callbackUrl;
		$this->consumer_key = $this->settings->get(self::PARAM_CONSUMER);
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
		$this->settings->setSetting(self::PARAM_TOKENS, null);

		$credentials = $this->requestCredentials(
			$this->base_url . $this->access_tocken_url . '?oauth_callback=' . $this->callback_url . '&oauth_verifier=' . $verifier,
			$token,
			$tokenSecret
		);

		$this->tokens = $credentials;
		$this->settings->setSetting(self::PARAM_TOKENS, serialize($credentials));
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
			throw new \Exception("An error occurred while requesting oauth token credentials");
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
				openssl_sign($stringToSign, $signature, $privateKeyId);
				openssl_free_key($privateKeyId);
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
