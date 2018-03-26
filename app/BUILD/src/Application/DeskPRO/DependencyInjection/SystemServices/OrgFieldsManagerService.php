<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\OrganizationFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class OrgFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new OrganizationFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefOrganization',
                'entity_name'       => 'DeskPRO:CustomDefOrganization',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataOrganization',
                'data_entity_name'  => 'DeskPRO:CustomDataOrganization',
            ]
        );

        return $m;
    }
}
