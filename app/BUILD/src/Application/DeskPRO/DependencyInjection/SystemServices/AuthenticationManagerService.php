<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AuthenticationManagerService
{
    public static function create(DeskproContainer $container, $interface = DP_INTERFACE)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $um */
        $um = $container->getSystemService('usersource_manager');

        /** @var \Application\DeskPRO\Auth\AuthSettings $as */
        $as = $container->getSystemService('auth_settings');

        /* @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $as */
        $aaf = $container->getSystemService('usersource_auth_adapter_factory');

        /* @var \Application\DeskPRO\NewSettings\SettingsBag $as */
        $app_settings = $container->getSettingsResolver()->getGlobalSettings();

        return new AuthenticationManager(
            $as,
            $um,
            $aaf,
            $app_settings,
            $interface == 'user' ? 'user' : 'agent'
        );
    }
}
