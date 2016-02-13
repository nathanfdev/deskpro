<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * https://bitbucket.org/atlassian_tutorial/atlassian-oauth-examples/src/d625161454d1ca97b4515c6147b093fac9a68f7e/php/?at=default.
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

        $this->tokens       = $this->service->getTokens();
        $this->callback_url = $callbackUrl;

        if ($authority = $service->getSSLAuthority()) {
            if ('system' === $authority) {
                $this->ssl_authority = $authority;
            }
            if ('disabled' === $authority) {
                $this->ssl_authority = false;
            }
        }
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
            $this->base_url.$this->request_token_url.'?oauth_callback='.$this->callback_url
        );
    }

    /**
     * @param $token
     * @param $tokenSecret
     * @param $verifier
     *
     * @throws Exception
     * @throws \Exception
     *
     * @return array
     */
    public function requestAuthCredentials($token, $tokenSecret, $verifier)
    {
        $this->service->setTokens(array());

        $credentials = $this->requestCredentials(
            $this->base_url.$this->access_tocken_url.'?oauth_callback='.$this->callback_url.'&oauth_verifier='.$verifier,
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
     * @throws Exception
     * @throws \Exception
     *
     * @return array
     */
    protected function requestCredentials($url, $token = false, $tokenSecret = false)
    {
        $client = $this->getClient($token, $tokenSecret);
        try {
            $response = $client->post($url)->send();
        } catch (\Exception $e) {
            // todo handle curl errors
            throw $e;
        }

        $body = (string) $response->getBody();

        $tokens = array();
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
     * @throws \Exception
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

        $this->client = new Client($this->base_url, array(
            Client::SSL_CERT_AUTHORITY => $this->ssl_authority,
        ));
        $privateKey = $this->private_key;

        $plugin = new OauthPlugin(array(
            'consumer_key'       => $this->consumer_key,
            'consumer_secret'    => $this->consumer_secret,
            'token'              => $token,
            'token_secret'       => $secret,
            'signature_method'   => 'RSA-SHA1',
            'signature_callback' => function ($stringToSign, $key) use ($privateKey) {

                $certificate = openssl_pkey_get_private($privateKey);
                $privateKeyId = openssl_get_privatekey($certificate);
                $signature = null;
                if (!@openssl_sign($stringToSign, $signature, $privateKeyId)) {
                    throw new ApiGeneralException('Invalid Private Key', 1004);
                }
                @openssl_free_key($privateKeyId);

                return $signature;
            },
        ));

        $this->client->addSubscriber($plugin);

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
