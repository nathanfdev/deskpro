<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class DownloadFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new FieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefDownload',
                'entity_name'       => 'DeskPRO:CustomDefDownload',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataDownload',
                'data_entity_name'  => 'DeskPRO:CustomDataDownload',
            ]
        );

        return $m;
    }
}
