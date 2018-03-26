<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\JobScheduler;

class JobQueueService
{
    public static function create(DeskproContainer $container)
    {
        $queue = new JobQueue($container->getEm(), new JobScheduler($container->getEm()));

        return $queue;
    }
}
