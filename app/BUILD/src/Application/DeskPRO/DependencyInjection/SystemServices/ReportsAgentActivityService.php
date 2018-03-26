<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\AgentActivity;

class ReportsAgentActivityService
{
    public static function create(DeskproContainer $container)
    {
        $x = new AgentActivity($container->getEm());

        return $x;
    }
}
