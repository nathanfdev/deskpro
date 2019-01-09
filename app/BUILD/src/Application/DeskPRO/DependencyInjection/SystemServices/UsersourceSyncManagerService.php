<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Usersource\Sync\Syncer\DbTableSyncer;
use Application\DeskPRO\Usersource\Sync\Syncer\LdapSyncer;
use Application\DeskPRO\Usersource\Sync\SyncerHelper;
use Application\DeskPRO\Usersource\Sync\SyncManager;

class UsersourceSyncManagerService
{
    public static function create(DeskproContainer $container)
    {
        $usLogger = null;
        $appEnv   = $container->get('deskpro.app_env');
        if ($appEnv->isDebug() || $appEnv->getConfig('logs.enable_usersource_log')) {
            // only instantiate the helper with the usersource logger if its enabled
            $usLogger = $container->getUsersourceLogger();
        }

        $helper = new SyncerHelper($container->getEm(), $container->get('validator'), $usLogger);

        $syncers = [];

        $syncers[] = new DbTableSyncer($helper);
        $syncers[] = new LdapSyncer($helper);

        $sm = new SyncManager($syncers, $container->getEm(), $container->getJobQueue(), $container->getSystemService('usersource_manager'), $helper);

        return $sm;
    }
}
