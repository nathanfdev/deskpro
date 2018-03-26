<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\AuthInterfaceSettings;
use Application\DeskPRO\Auth\AuthSettings;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AuthSettingsService
{
    public static function create(DeskproContainer $container, array $options = [])
    {
        $adapterFactory = $container->getSystemService('usersource_auth_adapter_factory');

        /** @var \Application\DeskPRO\Usersource\UsersourceManager $um */
        $um = $container->getSystemService('usersource_manager');

        ////////////////////////////////////////////////
        // User Interface Auth Settings
        $userAuthSettings = new AuthInterfaceSettings($adapterFactory);

        if ($usSsoBackground = $um->getAll()->configuredForUsers()->withBackgroundSso()->getFirstOrNull()) {
            $userAuthSettings->setBackgroundSsoEnabled(true);
            $userAuthSettings->setSsoUsersource($usSsoBackground);
        }

        if ($usSsoAuto = $um->getAll()->configuredForUsers()->withAutoSso()->getFirstOrNull()) {
            $userAuthSettings->setAutoSsoEnabled(true);
            $userAuthSettings->setSsoUsersource($usSsoAuto);
        }

        ////////////////////////////////////////////////
        // Agent Interface Auth Settings
        $agentAuthSettings = new AuthInterfaceSettings($adapterFactory);

        if ($usSsoBackground = $um->getAll()->configuredForAgents()->withBackgroundSso()->getFirstOrNull()) {
            $agentAuthSettings->setBackgroundSsoEnabled(true);
            $agentAuthSettings->setSsoUsersource($usSsoBackground);
        }

        if ($usSsoAuto = $um->getAll()->configuredForAgents()->withAutoSso()->getFirstOrNull()) {
            $agentAuthSettings->setAutoSsoEnabled(true);
            $agentAuthSettings->setSsoUsersource($usSsoAuto);
        }

        ////////////////////////////////////////////////
        // App Auth Settings
        return new AuthSettings($userAuthSettings, $agentAuthSettings);
    }
}
