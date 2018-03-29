<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\Actions\ActionDef\TicketActionDefManager;
use Doctrine\Common\Collections\ArrayCollection;

class TicketActionDefManagerService
{
    public static function create(DeskproContainer $container)
    {
        $defs = $container->getEm()->getRepository('DeskPRO:TicketActionDef')->getActions();
        if ($defs instanceof ArrayCollection) {
            $defs = $defs->toArray();
        }

        $x = new TicketActionDefManager($defs);

        return $x;
    }
}
