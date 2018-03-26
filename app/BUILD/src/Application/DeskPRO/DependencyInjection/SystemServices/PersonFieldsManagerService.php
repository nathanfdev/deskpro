<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class PersonFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new PersonFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefPerson',
                'entity_name'       => 'DeskPRO:CustomDefPerson',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataPerson',
                'data_entity_name'  => 'DeskPRO:CustomDataPerson',
            ]
        );

        return $m;
    }
}
