<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\TicketSlas;

class TicketSlasService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketSlas($container->getEm());

        return $x;
    }
}
