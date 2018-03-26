<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue\SupervisorRules;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobSupervisorException;
use DpSys\LowError\SystemErrorHandler;

/**
 * A job should not be in the "processing" state for more than 20 minutes, signal an error as there is unfinished work!
 */
class ProcessingTimeoutRule extends AbstractSupervisorRule
{
    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $date = new \DateTime('20 minutes ago');

        $query = $this->connection->executeQuery(
            '
            SELECT count(id) as total
            FROM jobs
            WHERE status = :processing_state
            AND date_touch < :twenty_mins_ago
            ',
            [
                'processing_state' => Job::STATUS_PROCESSING,
                'twenty_mins_ago'  => $date,
            ],
            [
                'processing_state' => 'string',
                'twenty_mins_ago'  => 'datetime',
            ]
        );
        $result = $query->fetch();
        if (is_array($result) and array_key_exists('total', $result)) {
            $total = $result['total'];
            if ($total > 0) {
                throw new JobSupervisorException("Found $total idle jobs that have been processing for longer than 20 minutes.");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attemptToFix()
    {
        // for now we are just going to abort the job and dump the job row to the log
        // via an exception
        $date = new \DateTime('20 minutes ago');

        $query = $this->connection->executeQuery(
            '
            SELECT *
            FROM jobs
            WHERE status = :processing_state
            AND date_touch < :twenty_mins_ago
            ',
            [
                'processing_state' => Job::STATUS_PROCESSING,
                'twenty_mins_ago'  => $date,
            ],
            [
                'processing_state' => 'string',
                'twenty_mins_ago'  => 'datetime',
            ]
        )
        ;
        $result = $query->fetchAll();

        $ids = array_map(function ($job_row) {
            return $job_row['id'];
        }, $result);

        if (!count($ids)) {
            return false;
        }

        $this->connection->executeUpdate(
            'UPDATE jobs SET status = :aborted, status_code = :exhausted WHERE id IN (:job_ids)',
            [
                'aborted'   => Job::STATUS_ABORTED,
                'exhausted' => Job::STATUS_CODE_EXHAUSTED,
                'job_ids'   => implode(', ', $ids),
            ]
        )
        ;

        SystemErrorHandler::logException(new \Exception(
            'ABORTED the following jobs due to timeout:'
            .json_encode($result)
        ), false);

        return false;
    }
}
