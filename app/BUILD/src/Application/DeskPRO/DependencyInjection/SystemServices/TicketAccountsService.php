<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\TicketAccounts\TicketAccounts;

class TicketAccountsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketAccounts($container->getEm());

        return $x;
    }
}
