<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\AppBundle\Settings\Model\UsersourceSettings;

/**
 * Class UsersourceSettingsResolver.
 */
class UsersourceSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    /**
     * @var UsersourceManager
     */
    private $usersourceManager;

    /**
     * Constructor.
     *
     * @param UsersourceManager          $usersourceManager
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(UsersourceManager $usersourceManager, BrandAwareSettingsResolver $settingsResolver)
    {
        parent::__construct($settingsResolver);

        $this->usersourceManager = $usersourceManager;
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
            ->setRegEnabled($this->getSetting('core.reg_enabled'))
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
