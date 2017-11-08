<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

use JMS\Serializer\Annotation as JMS;

class SerializedOauth1Connection implements Oauth1ProviderDetails, Oauth1ClientCredentials
{
    /** @var \JMS\Serializer\Serializer $serializer */

    /**
     * @param $serializedValue
     * @param \JMS\Serializer\SerializerInterface null $serializer
     *
     * @return SerializedOauth1Connection
     */
    public static function fromJSON($serializedValue, $serializer = null)
    {
        if (is_null($serializer)) {
            $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        }

        return $serializer->deserialize($serializedValue, self::class, 'json');
    }

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("providerName")
     *
     * @var string
     */
    private $providerName;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("urlTemporaryCredentials")
     *
     * @var string
     */
    private $urlTemporaryCredentials;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("urlAuthorize")
     *
     * @var string
     */
    private $urlAuthorize;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("urlAccessToken")
     *
     * @var string
     */
    private $urlAccessToken;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("urlResourceOwnerDetails")
     *
     * @var string
     */
    private $urlResourceOwnerDetails;

    // CREDENTIALS

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("rsaPrivateKey")
     *
     * @var string
     */
    private $rsaPrivateKey;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("urlRedirect")
     *
     * @var string
     */
    private $urlRedirect;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientId")
     *
     * @var string
     */
    private $clientId;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientSecret")
     *
     * @var string
     */
    private $clientSecret;

    // tokens

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("token")
     *
     * @var string
     */
    private $token;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("tokenSecret")
     *
     * @var string
     */
    private $tokenSecret;

    /**
     * @param Oauth1AuthorizationSession $session
     * @param array                      $options
     *
     * @return string
     */
    public function getAuthorizationUrl(Oauth1AuthorizationSession $session, $options = [])
    {
        return $session->refreshTemporaryCredentials($this, $this)->getAuthorizationUrl($this, $this);
    }

    /**
     * @param Oauth1AuthorizationSession $session
     * @param $oauthToken
     * @param $oauthVerifier
     *
     * @return Oauth1AccessToken
     */
    public function getAccessToken(Oauth1AuthorizationSession $session, $oauthToken, $oauthVerifier)
    {
        return $session->getAccessToken($this, $this, $oauthToken, $oauthVerifier);
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Get the URL for retrieving temporary credentials.
     *
     * @return string
     */
    public function getUrlTemporaryCredentials()
    {
        return $this->urlTemporaryCredentials;
    }

    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    public function getUrlAuthorization()
    {
        return $this->urlAuthorize;
    }

    /**
     * Get the URL retrieving token credentials.
     *
     * @return string
     */
    public function getUrlTokenCredentials()
    {
        return $this->urlAccessToken;
    }

    /**
     * Get the URL for retrieving user details.
     *
     * @return string
     */
    public function getUrlUserDetails()
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @return string
     */
    public function getRSAPrivateKey()
    {
        return $this->rsaPrivateKey;
    }

    /**
     * @return string
     */
    public function getUrlRedirect()
    {
        return $this->urlRedirect;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    // tokens

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
}
