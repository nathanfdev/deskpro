<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ProductFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new FieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefProduct',
                'entity_name'       => 'DeskPRO:CustomDefProduct',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataProduct',
                'data_entity_name'  => 'DeskPRO:CustomDataProduct',
                'disabled'          => $container->getSetting('core.use_product_fields') ? false : true,
            ]
        );

        return $m;
    }
}
