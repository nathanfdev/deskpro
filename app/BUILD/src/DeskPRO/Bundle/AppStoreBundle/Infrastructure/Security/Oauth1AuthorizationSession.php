<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

interface Oauth1AuthorizationSession
{
    /**
     * @param Oauth1ProviderDetails   $providerDetails
     * @param Oauth1ClientCredentials $credentials
     *
     * @throws OauthException
     *
     * @return Oauth1AuthorizationSession
     */
    public function refreshTemporaryCredentials(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials);

    /**
     * @param Oauth1ProviderDetails   $providerDetails
     * @param Oauth1ClientCredentials $credentials
     *
     * @return string
     */
    public function getAuthorizationUrl(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials);

    /**
     * @param Oauth1ProviderDetails   $providerDetails
     * @param Oauth1ClientCredentials $credentials
     * @param $oauthToken
     * @param $oauthVerifier
     *
     * @throws OauthException
     *
     * @return Oauth1AccessToken
     */
    public function getAccessToken(Oauth1ProviderDetails $providerDetails, Oauth1ClientCredentials $credentials, $oauthToken, $oauthVerifier);
}
