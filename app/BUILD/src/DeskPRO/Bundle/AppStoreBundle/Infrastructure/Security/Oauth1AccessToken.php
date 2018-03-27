<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

class Oauth1AccessToken implements \JsonSerializable
{
    /** @var string */
    private $oauthToken;

    /** @var string */
    private $oauthTokenSecret;

    /**
     * Oauth1AccessToken constructor.
     *
     * @param $oauthToken
     * @param $oauthTokenSecret
     */
    public function __construct($oauthToken, $oauthTokenSecret)
    {
        $this->oauthToken       = $oauthToken;
        $this->oauthTokenSecret = $oauthTokenSecret;
    }

    public function jsonSerialize()
    {
        return [
            'oauth_token'        => $this->oauthToken,
            'oauth_token_secret' => $this->oauthTokenSecret,
        ];
    }
}
