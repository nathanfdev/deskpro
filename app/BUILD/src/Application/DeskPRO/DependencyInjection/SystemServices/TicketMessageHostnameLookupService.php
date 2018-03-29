<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Net\Dns\Rdns\CachedRdns;
use Application\DeskPRO\Net\Dns\Rdns\RdnsNull;
use Application\DeskPRO\Net\Dns\Rdns\RdnsSocket;
use Application\DeskPRO\Tickets\MessageHostnameLookup;

class TicketMessageHostnameLookupService
{
    public static function create(DeskproContainer $container)
    {
        if (!$container->getSetting('rdns_ticket_messages') || !$container->getSetting('rdns_server')) {
            $rdns = new RdnsNull();
        } else {
            $rdns = new CachedRdns(
                $container->getEm(),
                new RdnsSocket($container->getSetting('rdns_server'), 4)
            );
        }

        return new MessageHostnameLookup($rdns);
    }
}
