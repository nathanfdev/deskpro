<?php

namespace Orb\Auth\Adapter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Log\Loggable;

class Twitter extends AbstractCallbackAdatper implements Loggable
{
    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $consumerKey;

    /**
     * @var string
     */
    protected $consumerSecret;

    /**
     * @param string $consumerKey    Your Twitter consumer key
     * @param string $consumerSecret Your Twitter consumer secret
     */
    public function __construct($consumerKey, $consumerSecret)
    {
        $this->consumerKey    = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(\Orb\Log\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        $oauthClient = $this->getOauthClient();

        try {
            $requestToken = $oauthClient->oauth('oauth/request_token', ['oauth_callback' => $this->getCallbackUrl()]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("[Twitter] authenticateInitialize exception: {$e->getCode()} {$e->getMessage()}", 'ERR');
            }

            return new Result(Result::FAILURE_EXCEPTION, null, [Result::MSG_EXCEPTION => $e]);
        }

        $state['orb_oauth_twitter_rtoken'] = $requestToken['oauth_token'];

        $redirectUrl = $oauthClient->url("oauth/authorize", ['oauth_token' => $requestToken['oauth_token']]);
        $result      = new Result(Result::REQUIRES_REDIRECT, null, [Result::MSG_REDIRECT => $redirectUrl]);

        if ($this->logger) {
            $this->logger->log("[Twitter] authenticateInitialize success: Token({$requestToken}) Redirect({$redirectUrl})", 'DEBUG');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        $oauth = $this->getOauthClient();

        if (!isset($state['orb_oauth_twitter_rtoken'])) {
            if ($this->logger) {
                $this->logger->log('[Twitter] authenticateCallback fail: Missing token', 'DEBUG');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'invalid_token', 'error_message' => 'Invalid verify token']);
        }

        if ($this->logger) {
            $this->logger->log("[Twitter] authenticateCallback token: {$state['orb_oauth_twitter_rtoken']}", 'ERR');
        }

        $accessToken = $oauth->oauth('oauth/access_token', array_merge($callback_data, ['oauth_token' => $state['orb_oauth_twitter_rtoken']]));
        unset($state['orb_oauth_twitter_rtoken']);

        $oauth   = $this->getOauthClient($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
        $content = $oauth->get("account/verify_credentials");

        if ($content->errors) {
            if ($this->logger) {
                $this->logger->log('[Twitter] authenticateCallback failed_verify_credentials', 'DEBUG');
            }

            return new Result(Result::FAILURE, null, ['error_code' => 'failed_verify_credentials', 'error_message' => 'Failed to call API service to verify credentials']);
        }

        if ($this->logger) {
            $this->logger->log("[Twitter] authenticateCallback verify_credentials: {$content}", 'DEBUG');
        }

        $rawUserInfo = [
            'access_token'        => $accessToken['oauth_token'],
            'access_token_secret' => $accessToken['oauth_token_secret'],
            'identity'            => $content->id_str,
            'identity_friendly'   => $content->screen_name,
            'fullname'            => $content->name,
            'url'                 => $content->url,
            'nickname'            => $content->screen_name,
            'raw'                 => $content,
        ];

        $identity = new \Orb\Auth\Identity($content->id, $rawUserInfo);
        $identity->setFriendlyIdentity($content->screen_name);

        $result = new Result(Result::SUCCESS, $identity);

        if ($this->logger) {
            $this->logger->log("[Twitter] authenticateCallback success: {$content->screen_name}", 'DEBUG');
        }

        return $result;
    }

    /**
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     *
     * @return TwitterOAuth
     */
    public function getOauthClient($oauthToken = null, $oauthTokenSecret = null)
    {
        return new TwitterOAuth($this->consumerKey, $this->consumerSecret, $oauthToken, $oauthTokenSecret);
    }
}
