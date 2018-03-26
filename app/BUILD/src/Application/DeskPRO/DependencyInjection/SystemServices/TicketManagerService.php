<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\TicketManager;

class TicketManagerService
{
    public static function create(DeskproContainer $container)
    {
        $s = new TicketManager($container);

        return $s;
    }
}
