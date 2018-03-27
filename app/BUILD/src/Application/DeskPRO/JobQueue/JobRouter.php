<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Job;

/**
 * The Job Router is responsible for instantiating the JobProcessor for a job and executing it.
 */
class JobRouter
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $connection;

    /**
     * @var \Application\DeskPRO\JobQueue\JobProcessorInterface[]
     */
    private $processors;

    public function __construct(Connection $connection, array $processors = [])
    {
        $this->connection = $connection;
        $this->processors = $processors;
    }

    /**
     * @param array $job an array of a "jobs" row from dbal
     */
    public function handle(array $job)
    {
        try {
            $processor = $this->findProcessor($job);
            $processor->execute($job);
        } catch (\Exception $e) {
            $this->markException($job, $e);
        }
    }

    /**
     * Add a processor to the router.
     *
     * @param JobProcessorInterface $processor
     */
    public function addProcessor(JobProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Does the actual job array -> job processor mapping and returns an instantiated JobProcessorInterface.
     *
     * @throws JobQueueException
     *
     * @return JobProcessorInterface
     */
    private function findProcessor(array $job)
    {
        foreach ($this->processors as $processor) {
            if ($processor->canHandle($job)) {
                return $processor;
            }
        }

        throw new JobQueueException(sprintf('No processor found for "%s"', $job['type']));
    }

    /**
     * We should not have to do this at this layer, but we will if needed.
     *
     * Processors are encouraged to handle their own errors gracefully.
     *
     * @param array $job the job row from the dbal
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    protected function markException(array $job = null, \Exception $e)
    {
        if (is_array($job) && array_key_exists('id', $job)) {
            App::getDb()->executeUpdate(
                '
                UPDATE jobs
                SET status = :error_status,
                    date_touch = :date_now,
                    date_last_try = :date_now,
                    log = :error_log,
                    log_summary = :log_summary,
                    num_tries = num_tries + 1,
                    has_warning = 1
                WHERE id = :job_id
                ',
                [
                    'error_status' => Job::STATUS_ERROR,
                    'date_now'     => new \DateTime(),
                    'job_id'       => $job['id'],
                    'error_log'    => $e->getMessage()."\n\n\n".$e->getTraceAsString(),
                    'log_summary'  => 'A system error occurred',
                ],
                [
                    'error_status' => 'string',
                    'date_now'     => 'datetime',
                    'job_id'       => 'integer',
                    'error_log'    => 'text',
                    'log_summary'  => 'string',
                ]
            );
        }
    }
}
