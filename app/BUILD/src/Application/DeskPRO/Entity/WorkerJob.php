<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Log\Logger;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A worker job is some task that needs to run regularly, or on a schedule.
 */
class WorkerJob extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Some runners can be configured to run a number of jobs at a time. This
     * fields groups them into named bundles.
     *
     * @var string
     */
    protected $worker_group = null;

    /**
     * The name of the job.
     *
     * @var string
     */
    protected $title = '';

    /**
     * What it does.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The PHP classname of the job executor.
     *
     * @var string
     */
    protected $job_class;

    /**
     * Options for the job.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The most feedbackl interval for this task to run.
     */
    protected $interval = 3600;

    /**
     * The last time this job was run.
     *
     * @var \DateTime
     */
    protected $last_run_date = null;

    /**
     * The last time this job was started.
     *
     * @var \DateTime
     */
    protected $last_start_date = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Is the task running right now?
     *
     * @return bool
     */
    public function getIsRunning()
    {
        if (!$this->last_start_date) {
            return false;
        }

        if ($this->last_start_date && !$this->last_run_date) {
            return true;
        }

        if ($this->last_start_date->getTimestamp() > $this->last_run_date->getTimestamp()) {
            return true;
        }

        return false;
    }

    /**
     * Guess if the task has crashed or did crash.
     *
     * @param int $threshold
     *
     * @return bool
     */
    public function getIsCrashed($threshold = 900)
    {
        // Only tasks thata re still running can be crashed
        if (!$this->getIsRunning()) {
            return false;
        }

        $start = $this->last_start_date->getTimestamp();
        $now   = time();

        if ($now - $start > $threshold) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getRunningTime()
    {
        // Only tasks thata re still running can be crashed
        if (!$this->getIsRunning()) {
            return 0;
        }

        $start = $this->last_start_date->getTimestamp();
        $now   = time();

        return $now - $start;
    }

    /**
     * Get the date of the next run.
     *
     * @return \DateTime
     */
    public function getNextRunDate()
    {
        if ($this->last_start_date) {
            $d = clone $this->last_start_date;
        } else {
            $d = new \DateTime();
        }

        $d->add(new \DateInterval('PT'.$this->interval.'S'));

        return $d;
    }

    /**
     * @return string
     */
    public function getNextRunRelativeTime()
    {
        $date = $this->getNextRunDate();
        $ts   = $date->getTimestamp();

        $diff = $ts - time();

        if ($diff < 1) {
            return 'immediately';
        }

        return \Orb\Util\Dates::secsToReadable($diff, 2, 'short');
    }

    /**
     * Get interval in readable Enlgish.
     *
     * @return string
     */
    public function getIntervalReadable()
    {
        return \Orb\Util\Dates::secsToReadable($this->interval, 5, 'short');
    }

    /**
     * @param \Application\DeskPRO\Log\Logger $logger
     * @param array                           $options
     *
     * @return \Application\DeskPRO\WorkerProcess\Job\AbstractJob
     */
    public function createJobObj(Logger $logger, array $options = [])
    {
        $classname = $this->job_class;

        $options = array_merge($this->options, $options);

        $job = new $classname($logger, $options);

        return $job;
    }

    /**
     * @return bool
     */
    public function isReady()
    {
        if (!$this->interval or !$this->last_start_date) {
            return true;
        }

        $cut = time() - $this->interval;
        if ($this->last_start_date->getTimestamp() < $cut) {
            return true;
        }

        return false;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\WorkerJob';
        $metadata->setPrimaryTable(['name' => 'worker_jobs']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'worker_group',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'worker_group',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'description',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'description',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'job_class',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'job_class',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'options',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'data',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'interval',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'run_interval',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'last_run_date',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'last_run_date',
            'dpApi'      => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'last_start_date',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'last_start_date',
            'dpApi'      => true,
        ]);
    }
}
