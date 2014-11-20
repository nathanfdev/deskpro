<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * A job
 *
 * @property $id
 * @property $type
 * @property $status
 * @property $status_code
 * @property $date_touch
 * @property $date_created
 * @property $date_last_try
 * @property $date_next_try
 * @property $priority
 * @property $num_tries
 * @property $log_summary
 * @property $log
 * @property array $data
 * @property $has_warnings
 * @property Job|null $original_job
 * @property Job[]|null $child_jobs
 * @property Job|null $depends_on_job
 * @property $worker_id
 */
class Job extends \Application\DeskPRO\Domain\DomainObject
{
    const STATUS_INSERTING  = 'inserting';
    const STATUS_WAITING    = 'waiting';
    const STATUS_RESERVED   = 'reserved';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE   = 'complete';
    const STATUS_ERROR      = 'error';
    const STATUS_REJECTED   = 'rejected';
    const STATUS_DELEGATED  = 'delegated';
    const STATUS_ABORTED    = 'aborted';

    const STATUS_CODE_SUCCESS      = 'success'; // completed successfully
    const STATUS_CODE_RESCHEDULED  = 'rescheduled'; // a job we depend on is not yet done, we will retry
    const STATUS_CODE_RETRYING     = 'retrying'; // failed but we are retrying
    const STATUS_CODE_EXHAUSTED    = 'exhausted'; // retried it a bunch of times, won't retry again
    const STATUS_CODE_INVALID_DATA = 'invalid_data'; // the job data (payload) was invalid in some way, or couldn't be processed

    /**
     * @var int
     */
    protected $id;

    /**
     * The job type, used by the Job Router to find the right Job Processor
     *
     * @var string
     */
    protected $type;

    /**
     * The status of the job
     *
     * Should be one of the STATUS_* const's of this class:
     *
     * inserting: The job is being inserted, but is not yet ready to be processed. This is a transient state.
     * waiting: The job has been fully inserted and is waiting to be processed.
     * reserved: The job has been reserved by a job processor.
     * processing: The job has started processing.
     * complete: The job has finished successfully.
     * error: The job has stopped due to error.
     * rejected: The job has been rejected and will not be retried.
     * delegated: The job has been delegated to an external job service.
     * aborted: The job was manually aborted/cancelled by the admin.
     *
     * @var string
     */
    protected $status;

    /**
     * Any code to further classify the status. For example, 'error' might have a status_code with 'server_error'
     * for an exception, or maybe 'expired' to mean that the job can't complete because necessary data is no longer
     * available.
     *
     * @var string
     */
    protected $status_code;

    /**
     * Date the last time a process "touched" this ticket. We'll use this in processors to prevent supervisors from
     * considering the job a timeout.
     *
     * @var \DateTime
     */
    protected $date_touch;

    /**
     * Date this job entered the "jobs" table
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Last time we processed this job
     *
     * @var \DateTime
     */
    protected $date_last_try;

    /**
     * If this DateTime is in the future, it won't be selected for execution
     *
     * @var \DateTime
     */
    protected $date_next_try;

    /**
     * The priority of the job, used in the Job Router select SQL.
     *
     * Order is DESC, so higher priority numbers are selected first
     *
     * @var int
     */
    protected $priority;

    /**
     * The number of times this job has been executed Job Processor (usually indicated failures if > 1)
     *
     * @var int
     */
    protected $num_tries;

    /**
     * A human readable summary of the job's execution
     *
     * @var string
     */
    protected $log_summary;

    /**
     * A more verbose log
     *
     * @var string
     */
    protected $log;

    /**
     * An array of data, or payload, that the job processor needs to execute this job (stored in the db as json)
     *
     * This MUST always be an array, even if its an empty array
     *
     * @var array
     */
    protected $data;

    /**
     * The last time this job was started
     *
     * @var \DateTime
     */
    protected $has_warning;

    /**
     * Null unless the job was created as a retry - if so, the originating job ID is stored here so we can query
     * it later. Note: You MUST specify the originating job, do not create a linked list of failed jobs.
     *
     * @var Job|null
     */
    protected $original_job;

    /**
     * Null unless the job was created as a retry - if so, the originating job ID is stored here so we can query
     * it later. Note: You MUST specify the originating job, do not create a linked list of failed jobs.
     *
     * @var Job[]|null
     */
    protected $child_jobs;

    /**
     * If a Job is set here, then the workers will make sure this $depends_on_job has a status of complete before
     * processing the current job, usually it will be re-scheduled to be run later if not.
     *
     * @var Job|null
     */
    protected $depends_on_job;

    /**
     * A unique worker ID. Any worker that spawns and uses the queue should use a unique ID here.
     *
     * @var string
     */
    protected $worker_id;

    public function __construct($type, array $data = array())
    {
        $this->date_created  = new \DateTime();
        $this->date_next_try = new \DateTime();
        $this->has_warning   = false;
        $this->status        = self::STATUS_INSERTING;
        $this->priority      = 0;
        $this->num_tries     = 0;
        $this->type          = $type;
        $this->data          = $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\WorkerJob')
            ->setChangeTrackingPolicyNotify()
            ->setTable('jobs')
        ;
        $builder->mapId();

        $builder->mapString('type', 50);
        $builder->mapString('status', 25);
        $builder->mapString('status_code', 25);
        $builder->mapString('worker_id', 128);
        $builder->mapDateTime('date_touch');
        $builder->mapDateTime('date_created');
        $builder->mapDateTime('date_next_try');
        $builder->mapDateTime('date_last_try');
        $builder->mapInteger('priority', false);
        $builder->mapInteger('num_tries', false);
        $builder->addField('data', 'json_array', array('nullable' => true));
        $builder->mapString('log_summary');
        $builder->mapText('log');
        $builder->mapBoolean('has_warning');
        $builder->addManyToOne('original_job', 'Application\DeskPRO\Entity\Job', 'child_jobs');
        $builder->addOneToMany('child_jobs', 'Application\DeskPRO\Entity\Job', 'original_job');
        $builder->addManyToOne('depends_on_job', 'Application\DeskPRO\Entity\Job');
    }

    public function reschedule(\DateTime $retryDate)
    {
        $this->setModelField('date_next_try', $retryDate);
        $this->setModelField('status', Job::STATUS_WAITING);
        $this->setModelField('status_code', Job::STATUS_CODE_RESCHEDULED);
        $this->setModelField('worker_id', null);
        $this->setModelField('date_touch', new \DateTime());
    }

    public function retry(\DateTime $retryDate)
    {
        $this->setModelField('date_next_try', $retryDate);
        $this->setModelField('status', Job::STATUS_WAITING);
        $this->setModelField('status_code', Job::STATUS_CODE_RETRYING);
        $this->setModelField('worker_id', null);
        $this->setModelField('date_touch', new \DateTime());
    }
}
