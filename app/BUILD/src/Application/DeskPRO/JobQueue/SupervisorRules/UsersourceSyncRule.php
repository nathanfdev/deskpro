<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Application\DeskPRO\JobQueue\Processor\UsersourceSyncProcessor;
use Doctrine\DBAL\Connection;

class UsersourceSyncRule extends AbstractSupervisorRule
{
    /**
     * Checks the business logic behind this rule. Returns null if all is well. If a rule is violated, it
     * should throw the JobSupervisorException with a detailed message of the problem it found.
     *
     * @throws JobSupervisorException
     */
    public function check()
    {
        // there must always exist a job with an "active" status, and it must not look
        // like its in an error/hanging state. usersource sync jobs should never be
        // processing for more than 1 minute.
        // if there is not a job with a running/schedules status, we need to fix that.
        $jobs = $this->connection->fetchAll(
            '
            SELECT *
            FROM jobs
            WHERE status IN (:running_or_scheduled_status)
            AND type = :sync
            ',
            [
                'running_or_scheduled_status' => [
                    Job::STATUS_WAITING,
                    Job::STATUS_PROCESSING,
                    Job::STATUS_RESERVED,
                ],
                'sync' => UsersourceSyncProcessor::JOB_TYPE,
            ],
            [
                'running_or_scheduled_status' => Connection::PARAM_STR_ARRAY,
                'sync'                        => \PDO::PARAM_STR,
            ]
        );

        if (count($jobs)) {
            foreach ($jobs as $job) {
                $touch   = new \DateTime($job['date_touch']);
                $fivemin = new \DateTime('-5 minutes');
                if ($job['status'] == Job::STATUS_PROCESSING && $touch < $fivemin) {
                    // abort any processing sync job that has been in that state for 5 minutes
                    $this->queue->abortJobId($job['id']);
                }
            }
        } else {
            // no sync jobs running or scheduled, fail the check
            $e = new JobSupervisorException('usersource sync is not scheduled to run');
            $e->markDoNotReportIfFixed();

            throw $e;
        }
    }

    /**
     * Only called if a JobSupervisorException is thrown in check(), which is always called first. This method
     * MAY attempt to fix the problem. It is optional, however, and any rule that cannot be fixed should just
     * have this method return false.
     *
     * @return bool true on successful fix, false on unsuccessful fix
     */
    public function attemptToFix()
    {
        // ensure the new job wont abort itself. if this data is present it will.
        $this->connection->exec('DELETE FROM tmp_data WHERE name = "'.UsersourceSyncProcessor::ABORT_JOB_TMP_DATA_NAME.'"');

        // add a new job
        $this->queue->add(UsersourceSyncProcessor::JOB_TYPE, [], new \DateTime('tomorrow 1am'));

        return true;
    }
}
