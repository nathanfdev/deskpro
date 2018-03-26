<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\People\PersonEditManager;

class PersonEditManagerService
{
    public static function create(DeskproContainer $container)
    {
        $s = new PersonEditManager(
            $container->get('doctrine.orm.entity_manager')
        );

        return $s;
    }
}
