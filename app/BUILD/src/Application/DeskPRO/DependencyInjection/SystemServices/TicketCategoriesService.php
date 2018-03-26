<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\TicketCategories;

class TicketCategoriesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketCategories($container->getEm());
        $x->setDefaultCategoryPreference($container->getSetting('core.default_ticket_cat'));

        return $x;
    }
}
