<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JobQueue\JobSupervisor;
use Application\DeskPRO\JobQueue\SupervisorRules\ProcessingTimeoutRule;
use Application\DeskPRO\JobQueue\SupervisorRules\ReservedTimeoutRule;
use Application\DeskPRO\JobQueue\SupervisorRules\UsersourceSyncRule;

class JobSupervisorService
{
    /**
     * @param DeskproContainer $container
     *
     * @return JobSupervisor
     */
    public static function create(DeskproContainer $container)
    {
        $conn = $container->get('doctrine.dbal.default_connection');

        $supervisor = new JobSupervisor($conn);
        $supervisor->addRule(new ProcessingTimeoutRule($conn, $container->getJobQueue()));
        $supervisor->addRule(new ReservedTimeoutRule($conn, $container->getJobQueue()));
        $supervisor->addRule(new UsersourceSyncRule($conn, $container->getJobQueue()));

        return $supervisor;
    }
}
