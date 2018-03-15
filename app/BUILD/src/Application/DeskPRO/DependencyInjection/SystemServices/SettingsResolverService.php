<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

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

        $departmentData = $container->getTicketDepartments();

        $resolver->setVirtual('using_department', function () use ($departmentData) {
            return count($departmentData->getAllIds()) > 1;
        }
        );

        return $resolver;
    }
}
