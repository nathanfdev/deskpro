<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AsseticManagerService
{
    public static function create(DeskproContainer $container, array $options = [])
    {
        $package_manager = $container->get('assets.packages.factory');
        $packages        = $package_manager->createPackages();

        $app_env = $container->get('deskpro.app_env');

        $manager = new \Application\DeskPRO\Assetic\AsseticManager(
            App::getConfigFromFile('assets'),
            rtrim($app_env->getWwwRoot().$packages->getUrl('', 'legacy_web'), '/'),
            'build'
        );

        if ($container->isScopeActive('request')) {
            $manager->setAssetHelper($container->get('assets.packages'));
        }

        return $manager;
    }
}
