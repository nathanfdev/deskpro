<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

interface Oauth1ClientCredentials
{
    /**
     * @return string
     */
    public function getRSAPrivateKey();

    /**
     * @return string
     */
    public function getUrlRedirect();

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return string
     */
    public function getClientSecret();
}
