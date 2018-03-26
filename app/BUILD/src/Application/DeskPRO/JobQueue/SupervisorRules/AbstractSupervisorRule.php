<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue\SupervisorRules;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\JobSupervisorRuleInterface;

abstract class AbstractSupervisorRule implements JobSupervisorRuleInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var JobQueue
     */
    protected $queue;

    public function __construct(Connection $connection, JobQueue $queue)
    {
        $this->connection = $connection;
        $this->queue      = $queue;
    }
}
