<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class TicketDepartmentsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new TicketDepartments($container->getEm());

        return $x;
    }
}
