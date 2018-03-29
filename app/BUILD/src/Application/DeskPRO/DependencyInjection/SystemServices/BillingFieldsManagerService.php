<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\BillingFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class BillingFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new BillingFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefBilling',
                'entity_name'       => 'DeskPRO:CustomDefBilling',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataBilling',
                'data_entity_name'  => 'DeskPRO:CustomDataBilling',
            ]
        );

        return $m;
    }
}
