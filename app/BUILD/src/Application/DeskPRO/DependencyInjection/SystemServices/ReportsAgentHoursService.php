<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\AgentHours;

class ReportsAgentHoursService
{
    public static function create(DeskproContainer $container)
    {
        $x = new AgentHours($container->getEm());

        return $x;
    }
}
