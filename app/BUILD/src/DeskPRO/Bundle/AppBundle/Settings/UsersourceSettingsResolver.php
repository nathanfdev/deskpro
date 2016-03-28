<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Settings;

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
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param UsersourceManager          $usersourceManager
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(UsersourceManager $usersourceManager, BrandAwareSettingsResolver $settingsResolver)
    {
        $this->usersourceManager = $usersourceManager;
        $this->settingsResolver  = $settingsResolver;
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
            ->setRegEnabled($this->settingsResolver->getSetting('core.reg_enabled'))
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
