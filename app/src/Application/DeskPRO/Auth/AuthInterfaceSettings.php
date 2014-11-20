<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
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
     * a URL or null, if null we use system wide default
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
     * Just a convenience method to give us the SSO Usersource's Adapters' Auth Adapter (heh)
     */
    public function getSsoAuthAdapter($displayContext = null)
    {
        return $this->adapterFactory->getAuthAdapter($this->usersource, $displayContext);
    }
}
