<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Usersource\UsersourceManager;

class UsersourceManagerService
{
    public static function create(DeskproContainer $container)
    {
        $usm = new UsersourceManager($container->getEm(), $container);

        return $usm;
    }
}
