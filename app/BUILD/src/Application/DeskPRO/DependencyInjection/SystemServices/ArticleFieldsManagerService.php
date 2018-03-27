<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ArticleFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new FieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefArticle',
                'entity_name'       => 'DeskPRO:CustomDefArticle',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataArticle',
                'data_entity_name'  => 'DeskPRO:CustomDataArticle',
            ]
        );

        return $m;
    }
}
