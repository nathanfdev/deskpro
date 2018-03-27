<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\TicketPriorities;

class TicketPrioritiesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketPriorities($container->getEm());
        $x->setDefaultPriorityPreference($container->getSetting('core.default_ticket_pri'));

        return $x;
    }
}
