<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Annotation as JMS;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * A job.
 *
 * @property $id
 * @property $type
 * @property $status
 * @property $status_code
 * @property $date_touch
 * @property $date_created
 * @property $date_last_try
 * @property \DateTime $date_next_try
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
 *
 * @JMS\ExclusionPolicy("all")
 */
class Job extends DomainObject
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
     * The unique job id.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The job type, used by the Job Router to find the right Job Processor.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * The status of the job.
     *
     * @var string
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
     * aborted: The job was manually aborted/cancelled by the admin
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $status;

    /**
     * Any code to further classify the status.
     *
     * @var string
     *
     * For example, 'error' might have a status_code with 'server_error'
     * for an exception, or maybe 'expired' to mean that the job can't complete because necessary data is no longer
     * available
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $status_code;

    /**
     * Date the last time a process "touched" this ticket.
     *
     * @var \DateTime
     *
     * We'll use this in processors to prevent supervisors from
     * considering the job a timeout
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_touch;

    /**
     * Date this job entered the "jobs" table.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Last time we processed this job.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_last_try;

    /**
     * If this DateTime is in the future, it won't be selected for execution.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("DateTime")
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
     * The number of times this job has been executed Job Processor (usually indicated failures if > 1).
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $num_tries;

    /**
     * A human readable summary of the job's execution.
     *
     * @JMS\Groups("details")
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $log_summary;

    /**
     * A more verbose log.
     *
     * @JMS\Groups("details")
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $log;

    /**
     * An array of data, or payload, that the job processor needs to execute this job (stored in the db as json).
     *
     * This MUST always be an array, even if its an empty array
     *
     * @JMS\Groups("details")
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $data;

    /**
     * The last time this job was started.
     *
     * @var \DateTime
     */
    protected $has_warning;

    /**
     * Null unless the job was created as a retry - if so, the originating job ID is stored here so we can query
     * it later. Note: You MUST specify the originating job, do not create a linked list of failed jobs.
     *
     * @JMS\Groups("DEFAULT")
     * @JMS\Expose()
     * @JMS\Type("integer")
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

    /**
     * Constructor.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, array $data = [])
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

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->setModelField('data', $data);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getDataKey($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setDataKey($key, $value)
    {
        $this->data[$key] = $value;
        $this->setModelField('data', $this->data);

        return $this;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     *
     * @return $this
     */
    public function setLog($log)
    {
        $this->setModelField('log', $log);

        return $this;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\WorkerJob')
            ->setChangeTrackingPolicyNotify()
            ->setTable('jobs');
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
        $builder->addField('data', 'json_array', ['nullable' => true]);
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
        $this->setModelField('status', self::STATUS_WAITING);
        $this->setModelField('status_code', self::STATUS_CODE_RESCHEDULED);
        $this->setModelField('worker_id', null);
        $this->setModelField('date_touch', new \DateTime());
    }

    public function retry(\DateTime $retryDate)
    {
        $this->setModelField('date_next_try', $retryDate);
        $this->setModelField('status', self::STATUS_WAITING);
        $this->setModelField('status_code', self::STATUS_CODE_RETRYING);
        $this->setModelField('worker_id', null);
        $this->setModelField('date_touch', new \DateTime());
    }

    public function abort()
    {
        $this->setModelField('status', self::STATUS_ABORTED);
        $this->setModelField('status_code', self::STATUS_CODE_EXHAUSTED);
        $this->setModelField('date_touch', new \DateTime());
    }
}
