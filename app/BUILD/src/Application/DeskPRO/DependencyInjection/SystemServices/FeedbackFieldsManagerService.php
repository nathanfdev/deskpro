<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\FeedbackFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class FeedbackFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new FeedbackFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefFeedback',
                'entity_name'       => 'DeskPRO:CustomDefFeedback',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataFeedback',
                'data_entity_name'  => 'DeskPRO:CustomDataFeedback',
            ]
        );

        return $m;
    }
}
