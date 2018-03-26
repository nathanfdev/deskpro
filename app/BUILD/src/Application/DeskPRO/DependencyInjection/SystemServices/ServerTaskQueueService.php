<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ServerTaskQueue\ServerTaskQueue;

class ServerTaskQueueService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ServerTaskQueue($container->getEm());

        return $x;
    }
}
