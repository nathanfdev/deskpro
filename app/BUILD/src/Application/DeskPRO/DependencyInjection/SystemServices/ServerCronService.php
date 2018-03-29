<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ServerCron\ServerCron;

class ServerCronService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ServerCron($container->getEm());

        return $x;
    }
}
