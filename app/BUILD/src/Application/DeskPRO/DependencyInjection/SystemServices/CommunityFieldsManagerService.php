<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\CommunityFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class CommunityFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new CommunityFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefCommunityTopic',
                'entity_name'       => 'DeskPRO:CustomDefCommunityTopic',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataCommunityTopic',
                'data_entity_name'  => 'DeskPRO:CustomDataCommunityTopic',
            ]
        );

        return $m;
    }
}
