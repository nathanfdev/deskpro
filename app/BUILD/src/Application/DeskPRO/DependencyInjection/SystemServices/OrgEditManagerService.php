<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Organizations\OrgEditManager;

class OrgEditManagerService
{
    public static function create(DeskproContainer $container)
    {
        $s = new OrgEditManager(
            $container->get('doctrine.orm.entity_manager')
        );

        return $s;
    }
}
