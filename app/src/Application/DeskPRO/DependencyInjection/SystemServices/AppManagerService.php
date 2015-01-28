<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\App\AppManager;
use Application\DeskPRO\App\AppServiceContainer;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Doctrine\Common\Collections\ArrayCollection;

class AppManagerService
{
    public static function create(DeskproContainer $container)
    {
        $em = $container->getEm();

        if (!defined('DP_BUILDING')) {
            $packages = $em->createQuery("
                SELECT package, asset
                FROM DeskPRO:AppPackage package
                LEFT JOIN package.assets asset
                ORDER BY package.title
            ")->execute();

                $apps = $em->createQuery("
                SELECT app, package, asset
                FROM DeskPRO:AppInstance app
                LEFT JOIN app.package package
                LEFT JOIN package.assets asset
            ")->execute();

                $usersources = $em->createQuery("
                SELECT usersource
                FROM DeskPRO:Usersource usersource
                LEFT JOIN usersource.app app
            ")->execute();
        } else {

            // this is constructed in the portal system (for app usersources) during portal cache warm up
            // cannot make db queries while building - nor is this necessary for building
            $packages = array();
            $apps = array();
            $usersources = array();

        }

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

        $app_paths = array(
            'default' => DP_ROOT.'/apps',
        );

        if (dp_get_config('app_paths')) {
            foreach (dp_get_config('app_paths') as $prefix => $path) {
                $app_paths[$prefix] = $path;
            }
        }

        $app_manager = new AppManager($packages, $apps, $app_paths, $app_service_container, $usersources);

        return $app_manager;
    }
}
