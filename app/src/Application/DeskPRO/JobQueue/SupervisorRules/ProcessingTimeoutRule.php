<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace Application\DeskPRO\JobQueue\SupervisorRules;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobSupervisorException;
use DeskPRO\Kernel\KernelErrorHandler;

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
            array(
                'processing_state' => Job::STATUS_PROCESSING,
                'twenty_mins_ago'  => $date,
            ),
            array(
                'processing_state' => 'string',
                'twenty_mins_ago'  => 'datetime',
            )
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
            array(
                'processing_state' => Job::STATUS_PROCESSING,
                'twenty_mins_ago'  => $date,
            ),
            array(
                'processing_state' => 'string',
                'twenty_mins_ago'  => 'datetime',
            )
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
            array(
                'aborted'   => Job::STATUS_ABORTED,
                'exhausted' => Job::STATUS_CODE_EXHAUSTED,
                'job_ids'   => implode(', ', $ids),
            )
        )
        ;

        KernelErrorHandler::logException(new \Exception(
            'ABORTED the following jobs due to timeout:'
            .json_encode($result)
        ), false);

        return false;
    }
}
