<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\TicketSatisfaction;

class ReportsTicketSatisfactionService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketSatisfaction($container->getEm());

        return $x;
    }
}
