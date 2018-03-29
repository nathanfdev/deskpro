<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\Overview;

class ReportsOverviewService
{
    public static function create(DeskproContainer $container)
    {
        $x = new Overview($container->getEm());

        return $x;
    }
}
