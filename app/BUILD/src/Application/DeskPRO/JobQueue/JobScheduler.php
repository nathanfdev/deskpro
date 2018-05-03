<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\Entity\Job;
use Doctrine\ORM\EntityManager;

/**
 * Handles job dependencies.
 *
 * Responsible for making a job depend on another before it is persisted, using its own logic to determine how different
 * job types depend on each other. Also responsible for checking to see if a job is ready to run now.
 */
class JobScheduler
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set the $depends_on_job field with a Job object if the given Job needs to be run after the $depends_on_job
     * In the event that multiple jobs ned to run before this one, the $depends_on_job chain should be setup correctly
     * A depends on B which depends on C which depends on D, etc.
     *
     * @param Job $job
     */
    public function schedule(Job $job)
    {
        // TODO: implement some scheduling logic. You should maybe add a "job_grouping" field to Job entity
        //       and somehow use that to determine if this job should depend on the last job in the group (or something)
        // currently no dependencies are setup, but this will be useful in future.
        // scheduler should not save the job if it sets a dep, the queue does the save after this
        // ex: $job->depends_on_job = $this->em->getRepository('DeskPRO:Job')->find(26);
    }

    /**
     * Before we execute ANY job, the scheduler is asked via this method if the Job is ready to be run. If not, it
     * will be rescheduled. Return TRUE if it is safe to run now, and false otherwise.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function isReady(Job $job)
    {
        // if we don't depend on a job then its ready now
        if (!$job->depends_on_job) {
            return true;
        }

        // if the $depends_on_job is complete, we are ready to run this one
        if ($job->depends_on_job->status == Job::STATUS_COMPLETE) {
            return true;
        }

        return false;
    }
}
