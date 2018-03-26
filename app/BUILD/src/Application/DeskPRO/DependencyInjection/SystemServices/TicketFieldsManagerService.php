<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\CustomFields\TicketFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class TicketFieldsManagerService
{
    public static function create(DeskproContainer $container)
    {
        $m = new TicketFieldManager(
            $container->get('doctrine.orm.entity_manager'),
            [
                'settings_handler'  => $container->getSettingsHandler(),
                'entity_class'      => 'Application\\DeskPRO\\Entity\\CustomDefTicket',
                'entity_name'       => 'DeskPRO:CustomDefTicket',
                'data_entity_class' => 'Application\\DeskPRO\\Entity\\CustomDataTicket',
                'data_entity_name'  => 'DeskPRO:CustomDataTicket',
            ]
        );

        return $m;
    }
}
