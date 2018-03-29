<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ServerMysqlStatus\ServerMysqlStatus;

class ServerMysqlStatusService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ServerMysqlStatus($container->getEm());

        return $x;
    }
}
