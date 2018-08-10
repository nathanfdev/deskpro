<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\Entity\Job;
use Doctrine\ORM\EntityManager;

class JobQueue
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var JobScheduler
     */
    private $scheduler;

    public function __construct(EntityManager $em, JobScheduler $scheduler)
    {
        $this->em        = $em;
        $this->scheduler = $scheduler;
    }

    /**
     * Allows adding a job with just the job type and payload.
     *
     * @param           $type
     * @param array     $data
     * @param \DateTime $nextTry
     *
     * @return Job
     */
    public function add($type, array $data, \DateTime $nextTry = null)
    {
        $job = new Job($type, $data);
        $this->addJob($job, $nextTry);

        return $job;
    }

    /**
     * allows adding a job directly.
     *
     * @param Job       $job
     * @param \DateTime $nextTry
     */
    public function addJob(Job $job, \DateTime $nextTry = null)
    {
        // schedule the job as waiting directly as we flush it into the db
        $job->status = Job::STATUS_WAITING;

        // if the user did not request a future date, schedule it to be run immediately
        $job->date_next_try = $nextTry ?: new \DateTime();

        // tells scheduler to perform logic before we persist
        $this->scheduler->schedule($job);

        // save and flush immediately
        $this->saveJob($job);
    }

    /**
     * This will usually be run if the isReady() check returns false. Responsible for ensuring this job will attempt
     * to run sometime in the future.
     *
     * @param Job $job
     */
    public function reschedule(Job $job)
    {
        $job->reschedule(new \DateTime('now + 15 seconds'));
        $this->saveJob($job);
    }

    public function retry(Job $job, \DateTime $when)
    {
        $job->retry($when);
        $this->saveJob($job);
    }

    public function abort(Job $job)
    {
        $job->abort();
        $this->saveJob($job);
    }

    /**
     * Determines if the job is ready to run now.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function isReady(Job $job)
    {
        return $this->scheduler->isReady($job);
    }

    /**
     * Useful proxy if you only have the job ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function isReadyByJobId($id)
    {
        return $this->isReady($this->getJob($id));
    }

    /**
     * Useful proxy if you only have the job ID.
     *
     * @param int $id
     */
    public function rescheduleByJobId($id)
    {
        $this->reschedule($this->getJob($id));
    }

    /**
     * Useful proxy if you only have the job ID.
     *
     * @param int $id
     */
    public function retryByJobId($id, \DateTime $when)
    {
        $this->retry($this->getJob($id), $when);
    }

    public function abortJobId($id)
    {
        $this->abort($this->getJob($id));
    }

    /**
     * Save a Job.
     *
     * @param Job $job
     */
    public function saveJob(Job $job)
    {
        // push it immediately to the queue
        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * Get a Job directly from the database (refreshes).
     *
     * @param int $id the job id
     *
     * @return Job|null
     */
    public function getJob($id)
    {
        $job = $this->em->getRepository('DeskPRO:Job')->find($id);
        $this->em->refresh($job);

        return $job;
    }
}
