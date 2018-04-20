<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\AppBundle\Settings\Model\UsersourceSettings;

/**
 * Class UsersourceSettingsResolver.
 */
class UsersourceSettingsResolver
{
    /**
     * @var UsersourceManager
     */
    private $usersourceManager;

    /**
     * @var AuthenticationManager
     */
    private $authManager;

    /**
     * Constructor.
     *
     * @param UsersourceManager     $usersourceManager
     * @param AuthenticationManager $authManager
     */
    public function __construct(UsersourceManager $usersourceManager, AuthenticationManager $authManager)
    {
        $this->usersourceManager = $usersourceManager;
        $this->authManager       = $authManager;
    }

    /**
     * {@inheritdoc}
     *
     * @return UsersourceSettings
     */
    public function getSettings()
    {
        $model = new UsersourceSettings();
        $model
            ->setHasAgentLoginForm($this->hasCapability('agent', UsersourceInfo::CAPABILITY_FORM_LOGIN))
            ->setHasUserLoginForm($this->hasCapability('user', UsersourceInfo::CAPABILITY_FORM_LOGIN))
            ->setRegEnabled($this->authManager->isRegistrationFormVisible())
        ;

        return $model;
    }

    /**
     * @param string $type
     * @param string $capability
     *
     * @return bool
     */
    public function hasCapability($type, $capability)
    {
        $usersources = $this->usersourceManager->getAll()->forInterface($type);

        return count($usersources->withCapability($capability)) > 0;
    }
}
