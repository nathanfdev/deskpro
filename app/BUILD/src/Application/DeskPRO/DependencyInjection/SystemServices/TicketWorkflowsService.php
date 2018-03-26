<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\TicketWorkflows;

class TicketWorkflowsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketWorkflows($container->getEm());
        $x->setDefaultWorkflowPreference($container->getSetting('core.default_ticket_work'));

        return $x;
    }
}
