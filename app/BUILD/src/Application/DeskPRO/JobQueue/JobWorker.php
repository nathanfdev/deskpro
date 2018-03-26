<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Job;
use DpSys\LowError\SystemErrorHandler;

/**
 * The worker is the heart of the JobQueue system, and sets the stage for the JobRouter to route the job
 * to its Processor.
 */
class JobWorker
{
    /**
     * @var string worker ID string
     */
    protected $workerId;

    /**
     * @var JobRouter the router
     */
    protected $jobRouter;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var JobQueue
     */
    private $queue;

    public function __construct(Connection $connection, JobRouter $jobRouter, JobQueue $queue)
    {
        $this->connection = $connection;
        $this->queue      = $queue;

        // a unique ID for this particular worker
        $this->workerId = uniqid();

        // create the JobRouter for this worker instance
        $this->jobRouter = $jobRouter;
    }

    /**
     * Goto work, starts the loop. Continues until max_time, or no jobs found.
     *
     * @param int $max_time_in_seconds max time for the loop to run (seconds)
     */
    public function work($max_time_in_seconds = 25)
    {
        //------------------------------
        // loop for a max N seconds
        //------------------------------

        // TODO: look much deeper into this algorithm. suspect it might be slowing down the WorkerProcess (cron).

        $workerStartTime = time();
        $workerEndTime   = $workerStartTime + $max_time_in_seconds;
        // if it can't find a job to execute, the loop ends
        while ($this->executeNextJob()) {
            // if its past the time limit of 25 seconds, we also end
            if (time() > $workerEndTime) {
                break;
            }
        }
    }

    /**
     * Execute a job directly via passing in a job ID.
     *
     * Note that this is not following the normal JobQueue rules. Use at your own risk.
     *
     * @param $id
     *
     * @return bool
     */
    public function executeJobById($id)
    {
        $job = $this->getJobById($id);

        return $this->executeJob($job);
    }

    /**
     * Pop the next job, mark it as processing, and attempt to handle it.
     *
     * @return bool whether there was a job process attempt made
     */
    protected function executeNextJob()
    {
        $job = $this->popJob();

        return $this->executeJob($job);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return array|null an array of the job, or null if none available to work on
     */
    protected function popJob()
    {
        /*
         * Reserve the next available job
         */
        $this->connection->executeUpdate(
            '
            UPDATE jobs
            SET worker_id = :this_worker_id,
                status = :reserved_status
            WHERE status = :waiting_status
            AND date_next_try <= :date_now
            AND worker_id IS NULL
            AND type != :importer_type
            ORDER BY priority DESC,
                     date_next_try ASC
            LIMIT 1
            ',
            [
                'this_worker_id'  => $this->workerId,
                'reserved_status' => Job::STATUS_RESERVED,
                'waiting_status'  => Job::STATUS_WAITING,
                'date_now'        => new \DateTime(),
                'importer_type'   => 'importer',
            ],
            [
                'this_worker_id'  => 'string',
                'reserved_status' => 'string',
                'waiting_status'  => 'string',
                'date_now'        => 'datetime',
                'importer_type'   => 'string',
            ]
        );

        /*
         * Fetch the next job reserved for me
         */
        $job = $this->connection->fetchAssoc(
            '
            SELECT *
            FROM jobs
            WHERE worker_id = :this_worker_id
            AND status = :reserved_status
            ',
            [
                'reserved_status' => Job::STATUS_RESERVED,
                'this_worker_id'  => $this->workerId,
            ],
            [
                'reserved_status' => 'string',
                'this_worker_id'  => 'string',
            ]
        );

        return $job ?: null;
    }

    /**
     * @param $job
     *
     * @return bool
     */
    protected function executeJob($job)
    {
        if ($this->looksLikeAJobArray($job)) {
            if (!$this->queue->isReadyByJobId($job['id'])) {
                $this->queue->rescheduleByJobId($job['id']);

                return true;
            }

            $this->markProcessing($job);

            try {
                $this->jobRouter->handle($job);

                return true;
            } catch (\Exception $e) {
                // the router layer and processor layer are responsible for recording failures and retries etc on their own
                // router is supposed to handle all situations and catch all errors and never throw
                // this is here to attempt to keep the queue moving in case of what should be next-to-impossible situations
                // this will eventually do something other than return true
                SystemErrorHandler::logException($e, false);

                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Worker calls this before executing a job.
     *
     * @param array $job the job row from the dbal
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    protected function markProcessing(array $job)
    {
        if ($this->looksLikeAJobArray($job)) {
            $this->connection->executeUpdate(
                '
                UPDATE jobs
                SET status = :processing_status,
                    date_touch = :date_now,
                    date_last_try = :date_now,
                    num_tries = num_tries + 1
                WHERE id = :job_id
                ',
                [
                    'processing_status' => Job::STATUS_PROCESSING,
                    'date_now'          => new \DateTime(),
                    'job_id'            => $job['id'],
                ],
                [
                    'processing_status' => 'string',
                    'date_now'          => 'datetime',
                    'job_id'            => 'integer',
                ]
            );
        }
    }

    /**
     * @param $id
     *
     * @return array|null
     */
    protected function getJobById($id)
    {
        return $this->connection->fetchAssoc(
            '
            SELECT *
            FROM jobs
            WHERE id = :job_id
            ',
            [
                'job_id' => $id,
            ],
            [
                'job_id' => 'integer',
            ]
        );
    }

    /**
     * @param mixed $job
     *
     * @return bool
     */
    protected function looksLikeAJobArray($job)
    {
        // can only be a job if we have a job array with an ID
        return is_array($job) && array_key_exists('id', $job);
    }
}
