<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;
use Orb\Auth\Adapter\SsoCapableInterface;

class AuthInterfaceSettings
{
    private $autoSso;
    private $backgroundSso;
    private $logoutRedirectUrl;

    /**
     * @var Usersource
     */
    private $usersource;

    /**
     * @var UsersourceAuthAdapterFactory
     */
    private $adapterFactory;

    public function __construct(
        UsersourceAuthAdapterFactory $adapterFactory
    ) {
        $this->adapterFactory    = $adapterFactory;
        $this->autoSso           = false;
        $this->backgroundSso     = false;
        $this->logoutRedirectUrl = null;
        $this->usersource        = null;
    }

    /**
     * @return mixed
     */
    public function isAutoSsoEnabled()
    {
        return $this->autoSso;
    }

    /**
     * @param mixed $autoSso
     */
    public function setAutoSsoEnabled($autoSso)
    {
        $this->autoSso = $autoSso;
    }

    /**
     * @return mixed
     */
    public function isBackgroundSsoEnabled()
    {
        return $this->backgroundSso;
    }

    /**
     * @param mixed $backgroundSso
     */
    public function setBackgroundSsoEnabled($backgroundSso)
    {
        $this->backgroundSso = $backgroundSso;
    }

    /**
     * a URL or null, if null we use system wide default.
     *
     * @return string|null
     */
    public function getLogoutRedirectUrl()
    {
        // give auth adapter a chance to override the admin's setting here (single sign-off compliance)
        if ($this->isAutoSsoEnabled() || $this->isBackgroundSsoEnabled()) {
            $adapter = $this->adapterFactory->getAuthAdapter($this->getSsoUsersource());
            if ($adapter instanceof SsoCapableInterface) {
                if ($url = $adapter->getLogoutRedirectUrl()) {
                    return $url;
                }
            }
        }

        return $this->logoutRedirectUrl;
    }

    /**
     * @param mixed $logoutRedirectUrl
     */
    public function setLogoutRedirectUrl($logoutRedirectUrl)
    {
        $this->logoutRedirectUrl = $logoutRedirectUrl;
    }

    /**
     * @return Usersource
     */
    public function getSsoUsersource()
    {
        return $this->usersource;
    }

    /**
     * @param Usersource $usersource
     */
    public function setSsoUsersource(Usersource $usersource = null)
    {
        $this->usersource = $usersource;
    }

    /**
     * Just a convenience method to give us the SSO Usersource's Adapters' Auth Adapter (heh).
     */
    public function getSsoAuthAdapter($displayContext = null)
    {
        return $this->adapterFactory->getAuthAdapter($this->usersource, $displayContext);
    }
}
