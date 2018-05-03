<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\Filters\FilterChangeDetector;

/**
 * Class TicketFilterChangeDetectorService.
 */
class TicketFilterChangeDetectorService
{
    /**
     * @param DeskproContainer $container
     *
     * @return FilterChangeDetector
     */
    public static function create(DeskproContainer $container)
    {
        return new FilterChangeDetector(
            $container->getEm(),
            $container->get('event_dispatcher'),
            $container->get('deskpro.notification.event_manager')
        );
    }
}
