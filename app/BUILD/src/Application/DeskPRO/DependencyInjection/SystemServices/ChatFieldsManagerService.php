<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\ChatFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ChatFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new ChatFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefChat',
                'entity_name'       => 'DeskPRO:CustomDefChat',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataChat',
                'data_entity_name'  => 'DeskPRO:CustomDataChat',
            ]
        );

        return $m;
    }
}
