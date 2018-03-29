<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

interface Oauth1ProviderDetails
{
    /**
     * @return string
     */
    public function getProviderName();

    /**
     * Get the URL for retrieving temporary credentials.
     *
     * @return string
     */
    public function getUrlTemporaryCredentials();
    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    public function getUrlAuthorization();

    /**
     * Get the URL retrieving token credentials.
     *
     * @return string
     */
    public function getUrlTokenCredentials();

    /**
     * Get the URL for retrieving user details.
     *
     * @return string
     */
    public function getUrlUserDetails();
}
