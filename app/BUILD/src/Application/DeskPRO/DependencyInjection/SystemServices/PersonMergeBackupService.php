<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\People\PersonMerge\Backup;
use Application\DeskPRO\People\PersonMerge\MergeBackup;

class PersonMergeBackupService
{
    /**
     * @param DeskproContainer $container
     *
     * @return MergeBackup
     */
    public static function create(DeskproContainer $container)
    {
        $dump = new Backup\PersonDump(
            $container->get('doctrine.orm.entity_manager')
        );

        $restore = new Backup\PersonRestore(
            $container->get('doctrine.orm.entity_manager')
        );

        $s = new MergeBackup(
            $container->get('doctrine.orm.entity_manager'),
            $dump,
            $restore
        );

        return $s;
    }
}
