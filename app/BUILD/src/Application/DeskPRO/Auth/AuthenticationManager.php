<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DpSys\LowError\SystemErrorHandler;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Identity;
use Orb\Auth\Result;

/**
 * AuthenticationManager ties the pieces of the Authentication system together and makes some executive decisions about
 * the authentication system during the flow of a request for a particular interface.
 *
 * The AuthenticationManager has the right answers for the CURRENT INTERFACE, freeing you from having to worry about which
 * interface we are on.
 *
 * Note: Not to be confused with the Symfony Security Component's AuthenticationManager. Quite different.
 */
class AuthenticationManager
{
    /**
     * The interface this request was made from. Relevant for various auth settings.
     *
     * @var string
     */
    private $interface;

    /**
     * @var UsersourceManager
     */
    private $usersourceManager;

    /**
     * The usersources relevant for this request.
     * Collection of usersources for this interface.
     *
     * @var \Application\DeskPRO\Usersource\UsersourceCollection|Usersource[]
     */
    private $usersourcesForInterface;

    /**
     * @var AuthSettings
     */
    private $authSettings;

    /**
     * The interface settings relevant for this request.
     *
     * @var \Application\DeskPRO\Auth\AuthInterfaceSettings
     */
    private $settings;

    /**
     * @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory
     */
    private $authAdapterFactory;

    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $appSettings;

    /**
     * @var string
     */
    private $authBy;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @param UsersourceManager            $usersourceManager    system service
     * @param AuthSettings                 $authSettings         system service
     * @param UsersourceAuthAdapterFactory $auth_adapter_factory
     * @param SettingsBag                  $appSettings
     * @param BrandStack                   $brandStack
     * @param string                       $interface            this MUST be "user" or "agent"
     */
    public function __construct(
        AuthSettings                 $authSettings,
        UsersourceManager            $usersourceManager,
        UsersourceAuthAdapterFactory $auth_adapter_factory,
        SettingsBag                  $appSettings,
        BrandStack                   $brandStack,
        $interface
    ) {
        $this->usersourceManager  = $usersourceManager;
        $this->authSettings       = $authSettings;
        $this->authAdapterFactory = $auth_adapter_factory;
        $this->interface          = $interface;
        $this->appSettings        = $appSettings;
        $this->brandStack         = $brandStack;

        $this->settings                = $interface === 'user' ? $authSettings->getUserInterfaceSettings() : $authSettings->getAgentInterfaceSettings();
        $this->usersourcesForInterface = $this->usersourceManager->getAll()->forInterface($interface);
        if ($interface === 'user') {
            $this->usersourcesForInterface = $this->usersourcesForInterface->forBrand($this->brandStack->getActive()->getBrand());
        }
    }

    /**
     * Tells us if we can use this usersource to log the user in.
     *
     * @param Usersource $usersource
     *
     * @return bool
     */
    public function isUsableUsersource(Usersource $usersource)
    {
        // if its not enabled for this interface, then no
        if (!$this->usersourcesForInterface->contains($usersource)) {
            return false;
        }

        // if this interface has auto sso, and this is not the sso usersource, then no
        if ($this->settings->isAutoSsoEnabled()) {
            if ($this->settings->getSsoUsersource()->id != $usersource->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Settings relevant to THIS request (interface aware).
     *
     * @return AuthInterfaceSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return UsersourceManager
     */
    public function getUsersourceManager()
    {
        return $this->usersourceManager;
    }

    /**
     * All usersources for this interface.
     *
     * @return \Application\DeskPRO\Usersource\UsersourceCollection
     */
    public function getUsersources()
    {
        return $this->usersourcesForInterface;
    }

    /**
     * Can we handle form logins?
     *
     * @return bool
     */
    public function hasFormLoginCapability()
    {
        return count($this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN)) > 0;
    }

    /**
     * Loop the usersources (in order) and try to authenticate the user. First yes wins.
     *
     * @param $identifier
     * @param $password
     *
     * @return Result
     */
    public function authenticateFormLogin($identifier, $password)
    {
        //------------------------------
        // Auth usersources that accept local input
        //------------------------------

        $usersources = $this->getFormLoginUsersources();
        foreach ($usersources as $us) {
            $adapter = $this->authAdapterFactory->getAuthAdapter($us);

            if ($adapter instanceof FormLoginInterface) {
                $adapter->setFormData(
                    [
                        'username' => $identifier,
                        'password' => $password,
                    ]
                );

                try {
                    $result = $adapter->authenticate();
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e, false);
                    $GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'] = $adapter;
                    $GLOBALS['DP_AUTH_EXCEPTION']         = $e;
                    continue;
                }

                if ($result->isValid()) {
                    $login_processor = new LoginProcessor($us, $result->getIdentity());
                    $person          = $login_processor->getPerson();

                    $identity     = new Identity($person->id, ['person' => $person]);
                    $result       = new Result(Result::SUCCESS, $identity);
                    $this->authBy = $us->source_type;

                    return $result;
                }
            }
        }

        return new Result(Result::FAILURE_INVALID_CREDS);
    }

    /**
     * @return UsersourceAuthAdapterFactory
     */
    public function getAuthAdapterFactory()
    {
        return $this->authAdapterFactory;
    }

    /**
     * @return \Application\DeskPRO\Usersource\UsersourceCollection
     */
    public function getFormLoginUsersources()
    {
        return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN);
    }

    /**
     * Are we displaying any extra login icons?
     *
     * @return bool
     */
    public function hasLoginIconUsersources()
    {
        return count($this->getLoginIconUsersources()) > 0;
    }

    /**
     * Usersources that have an icon to display to login.
     *
     * @return \Application\DeskPRO\Usersource\UsersourceCollection
     */
    public function getLoginIconUsersources()
    {
        return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN);
    }

    /**
     * Are we displaying any extra login buttons?
     *
     * @return bool
     */
    public function hasLoginTextButtonUsersources()
    {
        return count($this->getLoginTextButtonUsersources()) > 0;
    }

    /**
     * Usersources that have a button to display to login.
     *
     * @return \Application\DeskPRO\Usersource\UsersourceCollection
     */
    public function getLoginTextButtonUsersources()
    {
        return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN);
    }

    /**
     * This was a VERY confusing capability to decipher. The method in LoginController had an algorithm
     * using the form login capability. Emulated this functionality here for B.C.
     *
     * @return \Application\DeskPRO\Usersource\UsersourceCollection
     */
    public function getForgotPasswordUsersources()
    {
        return $this->getUsersources()->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN);
    }

    /**
     * Can we handle user registration requests?
     *
     * @return bool
     */
    public function hasRegistrationCapability()
    {
        foreach ($this->usersourcesForInterface as $usersource) {
            if ($usersource->app === null && $usersource->getOption('reg_enabled')) {
                return true;
            }
        }

        return false;
    }

    public function isRememberMeEnabled()
    {
        return $this->appSettings->get('core.enable_user_rememberme');
    }

    /**
     * Has at least one usersource that can redirect to "lost password".
     *
     * @return bool
     */
    public function hasForgotPasswordUsersources()
    {
        return $this->isDeskPROEnabled();
    }

    public function isDeskPROEnabled($interface = null)
    {
        if (null === $interface) { // if we have a usersource that doesn't have an app (which always means the DeskPRO usersource)
            foreach ($this->usersourcesForInterface as $usersource) {
                if ($usersource->app === null) {
                    return true;
                }
            }

            return false;
        }

        // clone the auth manager except make it for the specific interface, not the default
        $authManager = $this->cloneForInterface($interface);

        return $authManager->isDeskPROEnabled();
    }

    /*********************************************
    #------------------------------
    # Code below is used for presentation purposes (in twig)
    # Code above is used internally for processing requests
    #------------------------------
    *********************************************/

    /**
     * Is there anything on the website to show the user when it comes to auth? (login sidebar, registration, reg page, etc)
     * For example, if redirect SSO is enabled, then this would be false. If for some reason only background SSO was
     * set, and no usersource displayed anything, then this is also false.
     *
     * @return bool
     */
    public function isAuthVisible()
    {
        if ($this->settings->isAutoSsoEnabled()) {
            return false;
        }

        // if no usersource has a visible capability
        return count(
                $this->usersourcesForInterface->withCapability(
                    [
                        UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN,
                        UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN,
                        UsersourceInfo::CAPABILITY_FORM_LOGIN,
                        UsersourceInfo::CAPABILITY_WIDGET_OVERLAY_BTN,
                        UsersourceInfo::CAPABILITY_NEW_COMMENT_TAB,
                    ]

                )
            ) != 0
        ;
    }

    /**
     * @return bool
     */
    public function isRegistrationFormVisible()
    {
        return $this->isAuthVisible() && $this->hasRegistrationCapability();
    }

    /**
     * @param $interface
     *
     * @return AuthenticationManager
     */
    public function cloneForInterface($interface)
    {
        return new self(
            $this->authSettings,
            $this->usersourceManager,
            $this->authAdapterFactory,
            $this->appSettings,
            $this->brandStack,
            $interface
        );
    }

    public function isLoginFormVisible()
    {
        return $this->isAuthVisible() && $this->hasFormLoginCapability();
    }

    public function isForgotPasswordVisible()
    {
        return $this->hasForgotPasswordUsersources();
    }

    public function getInterface()
    {
        return $this->interface;
    }

    public function getAuthBy()
    {
        return $this->authBy;
    }
}
