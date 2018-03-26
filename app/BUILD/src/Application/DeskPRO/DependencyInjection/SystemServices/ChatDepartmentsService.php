<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Departments\ChatDepartments;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ChatDepartmentsService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ChatDepartments($container->getEm());

        return $x;
    }
}
