<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Reports\Billing;

class ReportsBillingService
{
    public static function create(DeskproContainer $container)
    {
        $x = new Billing($container->getEm());

        return $x;
    }
}
