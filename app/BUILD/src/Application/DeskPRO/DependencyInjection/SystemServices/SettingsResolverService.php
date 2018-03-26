<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\NewSettings\Loader\BrandSettingsLoader;
use Application\DeskPRO\NewSettings\Loader\DbGlobalSettingsTableLoader;
use Application\DeskPRO\NewSettings\Loader\GlobalsArrayLoader;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;

class SettingsResolverService
{
    public static function create(DeskproContainer $container)
    {
        // we use a different class in the "test" env so that we can easily manipulate the settings on the fly for test purposes
        $env = $container->getParameter('kernel.environment');

        /** @var \Application\DeskPRO\Cache\Adapter\SimpleArrayCache $simple_array_cache */
        $simple_array_cache = $container->get('cache.simple_array');

        // loaders, in proper order. first loader is treated as the default settings.
        $loaders = [
            $container->getSystemService('default_settings_loader'),
            new DbGlobalSettingsTableLoader($container->getEm()->getConnection(), $simple_array_cache),
            new GlobalsArrayLoader($container->get('deskpro.app_env'), $simple_array_cache),
        ];

        // brand settings loader is a special loader, injected directly
        $resolver = new SettingsResolver(
            $loaders,
            $simple_array_cache,
            new BrandSettingsLoader($container->getEm()->getConnection(), $simple_array_cache)
        );

        $container->get('deskpro.feature_flags')->_setSettingsResolver($resolver);

        $resolver->setVirtual(
            'default_timezone', function ($settings) {
                $settings = new SettingsBag($settings);

                try {
                    $timezone_string = $settings->get('core.default_timezone');
                    $tz = new \DateTimeZone($timezone_string);
                } catch (\Exception $e) {
                    $tz = new \DateTimeZone('UTC');
                }

                return $tz;
            }
        );

        $resolver->setVirtual('using_department', function () use ($container) {
            global $DP_ENV;
            if ($DP_ENV->getRuntimeVar('is_building', false)) {
                return true;
            }

            try {
                $departmentData = $container->getTicketDepartments();

                return count($departmentData->getAllIds()) > 1;
            } catch (\Exception $e) {
                return true;
            }
        }
        );

        return $resolver;
    }
}
