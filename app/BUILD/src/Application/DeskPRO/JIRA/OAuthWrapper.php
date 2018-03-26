<?php

/**
 * https://bitbucket.org/atlassian_tutorial/atlassian-oauth-examples/src/d625161454d1ca97b4515c6147b093fac9a68f7e/php/?at=default.
 */

namespace Application\DeskPRO\JIRA;

use Application\DeskPRO\Service\JIRA;
use DeskPRO\Component\Util\GuzzleOauthSubscriber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;

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

    protected $ssl_authority = true;

    public function __construct(JIRA $service, $callbackUrl = null)
    {
        $this->service = $service;

        if (!$this->base_url = rtrim($this->service->getUrl(), '/')) {
            throw new ApiGeneralException('JIRA base url is required', 1000);
        }

        if (!$this->private_key = $this->service->getPrivateKey()) {
            throw new ApiGeneralException('JIRA private key is required', 1001);
        }

        if (!$this->consumer_key = $this->service->getConsumerKey()) {
            throw new ApiGeneralException('JIRA consumer key is required', 1002);
        }

        $this->tokens        = $this->service->getTokens();
        $this->callback_url  = $callbackUrl;
        $this->ssl_authority = $service->getSSLAuthority();
    }

    /**
     * @return array
     */
    public function requestTempCredentials()
    {
        if (!empty($this->tokens['oauth_token'])) {
            $this->tokens = [];
        }

        return $this->requestCredentials(
            $this->request_token_url.'?oauth_callback='.$this->callback_url
        );
    }

    /**
     * @param $token
     * @param $tokenSecret
     * @param $verifier
     *
     * @return array
     */
    public function requestAuthCredentials($token, $tokenSecret, $verifier)
    {
        $this->service->setTokens([]);

        $credentials = $this->requestCredentials(
            $this->access_tocken_url.'?oauth_callback='.$this->callback_url.'&oauth_verifier='.$verifier,
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
     *
     * @throws ApiCoreException
     * @throws ApiErrorsException
     * @throws ApiGeneralException
     *
     * @return array
     */
    protected function requestCredentials($url, $token = false, $tokenSecret = false)
    {
        $client = $this->getClient($token, $tokenSecret);
        try {
            $response = $client->post($url);
            $body     = (string) $response->getBody();
        } catch (ClientException $e) {
            $response = (string) $e->getResponse()->getBody();
            $json     = @\json_decode($response, 1);

            if (!empty($json['errors'])) {
                throw new ApiErrorsException($json['errors'], $e);
            }

            if (!empty($json['errorMessages'])) {
                throw new ApiCoreException($json['errorMessages'], $e);
            }

            throw $e;
        }

        $tokens = [];
        parse_str($body, $tokens);

        if (empty($tokens)) {
            throw new ApiGeneralException(sprintf(
                'Bad response from host. Expected urlencoded string but "%s" received.', substr($body, 0, 200)
            ), 1003);
        }

        if (!isset($tokens['oauth_token'])) {
            throw new ApiGeneralException(
                'Bad response from host. No OAuth token provided.', 1004);
        }

        return $this->tokens = $tokens;
    }

    /**
     * @param null $token
     * @param null $tokenSecret
     *
     * @return Client
     */
    public function getClient($token = null, $tokenSecret = null)
    {
        if (!$token && $this->client) {
            return $this->client;
        }

        $token  = $token ?: (isset($this->tokens['oauth_token']) ? $this->tokens['oauth_token'] : null);
        $secret = $tokenSecret ?: (isset($this->tokens['oauth_token_secret']) ? $this->tokens['oauth_token_secret'] : null);

        $stack      = HandlerStack::create();
        $middleware = new GuzzleOauthSubscriber([
            'consumer_key'           => $this->consumer_key,
            'consumer_secret'        => $this->consumer_secret,
            'token'                  => $token,
            'token_secret'           => $secret,
            'signature_method'       => GuzzleOauthSubscriber::SIGNATURE_METHOD_RSA,
            'private_key'            => $this->private_key,
            'private_key_passphrase' => '',
        ]);
        $stack->push($middleware);

        $this->client = new Client([
            'base_uri' => $this->base_url,
            'handler'  => $stack,

            RequestOptions::VERIFY => $this->ssl_authority,
            RequestOptions::AUTH   => 'oauth',
        ]);

        return $this->client;
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->base_url.sprintf($this->authorization_url, urlencode($this->tokens['oauth_token']));
    }
}
