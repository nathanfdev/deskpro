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
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Class AppManifestAuthor.
 */
class SerializedOauthConnection
{
    /** @var \JMS\Serializer\Serializer $serializer */

    /**
     * @param $serializedValue
     * @param \JMS\Serializer\SerializerInterface null $serializer
     * @return SerializedOauthConnection
     */
    public static function fromJSON($serializedValue, $serializer = null)
    {
        if (is_null($serializer)) {
            $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        }

        return $serializer->deserialize($serializedValue, SerializedOauthConnection::class, 'json');
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

    public function getAuthorizationUrl(array $options = [])
    {
        $provider = new GenericProvider([
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->urlRedirect
        ]);


        return $provider->getAuthorizationUrl($options);
    }

    /**
     * @param string $grant
     * @param array $options
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        $provider = new GenericProvider([
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->urlRedirect
        ]);

        return $provider->getAccessToken($grant, $options);
    }


    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName(string $providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * @return string
     */
    public function getUrlAuthorize(): string
    {
        return $this->urlAuthorize;
    }

    /**
     * @param string $urlAuthorize
     */
    public function setUrlAuthorize(string $urlAuthorize)
    {
        $this->urlAuthorize = $urlAuthorize;
    }

    /**
     * @return string
     */
    public function getUrlAccessToken(): string
    {
        return $this->urlAccessToken;
    }

    /**
     * @param string $urlAccessToken
     */
    public function setUrlAccessToken(string $urlAccessToken)
    {
        $this->urlAccessToken = $urlAccessToken;
    }

    /**
     * @return string
     */
    public function getUrlResourceOwnerDetails(): string
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @param string $urlResourceOwnerDetails
     */
    public function setUrlResourceOwnerDetails(string $urlResourceOwnerDetails)
    {
        $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getUrlRedirect(): string
    {
        return $this->urlRedirect;
    }

    /**
     * @param string $urlRedirect
     */
    public function setUrlRedirect(string $urlRedirect)
    {
        $this->urlRedirect = $urlRedirect;
    }

}
