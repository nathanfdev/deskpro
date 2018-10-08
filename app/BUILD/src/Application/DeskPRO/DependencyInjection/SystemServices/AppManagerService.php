<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\App\AppManager;
use Application\DeskPRO\App\AppServiceContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppPackage;
use Doctrine\Common\Collections\ArrayCollection;

class AppManagerService
{
    public static function createAppManager(DeskproContainer $container)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        $em = $container->getEm();

        $apps = $em->createQuery('
            SELECT app, package
            FROM DeskPRO:AppInstance app
            LEFT JOIN app.package package
        ')->execute();

        if (count($apps)) {
            $names = array_map(function ($a) {
                return $a->package->name;
            }, $apps);

            // This loads assets for installed apps
            // into the EM so we dont have a query-per-app
            $em->createQuery('
                SELECT partial package.{name}, asset
                FROM DeskPRO:AppPackage package
                LEFT JOIN package.assets asset
                WHERE package.name IN (:names)
                ORDER BY package.title
            ')->execute(['names' => $names]);
        }

        $packages = $em->createQuery('
            SELECT package
            FROM DeskPRO:AppPackage package
            ORDER BY package.title
        ')->execute();

        $usersources = $em->createQuery('
            SELECT usersource
            FROM DeskPRO:Usersource usersource
            LEFT JOIN usersource.app app
        ')->execute();

        if ($apps instanceof ArrayCollection) {
            $apps = $apps->toArray();
        }
        if ($packages instanceof ArrayCollection) {
            $packages = $packages->toArray();
        }
        if ($usersources instanceof ArrayCollection) {
            $usersources = $usersources->toArray();
        }

        $app_service_container = new AppServiceContainer($container);

        $app_paths = [
            'default' => DP_ROOT.'/apps',
        ];

        $env = $container->get('deskpro.app_env');
        if ($env->getConfig('paths.app_paths')) {
            foreach ($env->getConfig('paths.app_paths') as $prefix => $path) {
                $app_paths[$prefix] = $path;
            }
        }

        $packages = array_filter($packages, function (AppPackage $p) use ($DP_ENV) {
            if ($p->isCloudOnly()) {
                return defined('DPC_IS_CLOUD') || $DP_ENV->getConfig('env.server_id') === 'builder.deskprodemo.com';
            }

            return true;
        });

        $app_manager = new AppManager($packages, $apps, $app_paths, $app_service_container, $usersources);

        return $app_manager;
    }

    public static function create(DeskproContainer $container)
    {
        // the legacy SystemServices will call this create(),
        // but we want to use the DIC
        return $container->get('deskpro.apps.manager');
    }
}
