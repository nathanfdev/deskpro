<?php

namespace DeskPRO\Bundle\AppStoreBundle\Oauth1;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1AccessToken;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1AuthorizationSession;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1ClientCredentials;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\Oauth1ProviderDetails;
use League\OAuth1\Client\Credentials\TemporaryCredentials;

class AuthorizationSession implements Oauth1AuthorizationSession
{
    /** @var string */
    private $token;

    /** @var string */
    private $secret;

    /**
     * @param AuthorizationSession $session
     *
     * @return string
     */
    public static function serialize(AuthorizationSession $session)
    {
        return json_encode([
            'token'  => $session->getToken(),
            'secret' => $session->getSecret(),
        ]);
    }

    /**
     * @param $string
     *
     * @return AuthorizationSession|null
     */
    public static function unserialize($string)
    {
        $data = json_decode($string, $assoc = true);
        if (
            is_array($data)
            && array_key_exists('token', $data)
            && is_string($data['token'])
            && array_key_exists('secret', $data)
            && is_string($data['secret'])
        ) {
            $session         = new self();
            $session->token  = $data['token'];
            $session->secret = $data['secret'];

            return $session;
        }
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param Oauth1ProviderDetails   $providerDetails
     * @param Oauth1ClientCredentials $credentials
     *
     * @return Client
     */
    public function createClient(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials)
    {
        return Client::factory($providerDetails, $credentials);
    }

    public function refreshTemporaryCredentials(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials)
    {
        $credentials  = $this->createClient($providerDetails, $credentials)->getTemporaryCredentials();
        $this->token  = $credentials->getIdentifier();
        $this->secret = $credentials->getSecret();

        return $this;
    }

    public function getAuthorizationUrl(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials)
    {
        $temporary = $this->getTemporaryCredentials();

        return $this->createClient($providerDetails, $credentials)->getAuthorizationUrl($temporary);
    }

    public function getAccessToken(
        Oauth1ProviderDetails $providerDetails,
        Oauth1ClientCredentials $credentials,
        $oauthToken,
        $oauthVerifier
    ) {
        $temporaryCredentials = $this->getTemporaryCredentials();
        $tokenCredentials          = $this->createClient($providerDetails, $credentials)
            ->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier)
        ;

        return new Oauth1AccessToken($tokenCredentials->getIdentifier(), $tokenCredentials->getSecret());
    }

    /**
     * @return TemporaryCredentials
     */
    public function getTemporaryCredentials()
    {
        $temporary = new TemporaryCredentials();
        $temporary->setIdentifier($this->token);
        $temporary->setSecret($this->secret);

        return $temporary;
    }
}
