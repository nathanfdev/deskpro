<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

use JMS\Serializer\Annotation as JMS;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Class AppManifestAuthor.
 */
class SerializedOauth2Connection
{
    /** @var \JMS\Serializer\Serializer $serializer */

    /**
     * @param $serializedValue
     * @param \JMS\Serializer\SerializerInterface null $serializer
     * @return SerializedOauth2Connection
     */
    public static function fromJSON($serializedValue, $serializer = null)
    {
        if (is_null($serializer)) {
            $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        }

        return $serializer->deserialize($serializedValue, SerializedOauth2Connection::class, 'json');
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

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("scopes")
     *
     * @var string
     */
    private $scopes;


    public function getAuthorizationUrl(array $options = [])
    {
        $provider = new GenericProvider([
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->urlRedirect,
            'scopes' => $this->scopes
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
            'redirectUri' => $this->urlRedirect,
            'scopes' => $this->scopes
        ]);

        return $provider->getAccessToken($grant, $options);
    }

    /**
     * @param $code
     * @param array $extraParams
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getAccessTokenWithAuthorizationCode($code, array $extraParams = [])
    {
        $params = array_merge($extraParams, ['code' => $code]);
        return $this->getAccessToken('authorization_code', $params);
    }

    /**
     * @param $token
     * @param array $extraParams
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getAccessTokenWithRefreshToken($token, array $extraParams)
    {
        $params = array_merge($extraParams, ['refresh_token' => $token]);
        return $this->getAccessToken('refresh_token', $params);
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * @return string
     */
    public function getUrlAuthorize()
    {
        return $this->urlAuthorize;
    }

    /**
     * @param string $urlAuthorize
     */
    public function setUrlAuthorize($urlAuthorize)
    {
        $this->urlAuthorize = $urlAuthorize;
    }

    /**
     * @return string
     */
    public function getUrlAccessToken()
    {
        return $this->urlAccessToken;
    }

    /**
     * @param string $urlAccessToken
     */
    public function setUrlAccessToken($urlAccessToken)
    {
        $this->urlAccessToken = $urlAccessToken;
    }

    /**
     * @return string
     */
    public function getUrlResourceOwnerDetails()
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @param string $urlResourceOwnerDetails
     */
    public function setUrlResourceOwnerDetails($urlResourceOwnerDetails)
    {
        $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getUrlRedirect()
    {
        return $this->urlRedirect;
    }

    /**
     * @param string $urlRedirect
     */
    public function setUrlRedirect($urlRedirect)
    {
        $this->urlRedirect = $urlRedirect;
    }

}
