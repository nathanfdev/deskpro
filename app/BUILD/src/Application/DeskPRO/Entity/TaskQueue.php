<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\TaskQueueJob\AbstractJob;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class TaskQueue.
 */
class TaskQueue extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $runner_class;

    /**
     * @var array
     */
    protected $task_data = [];

    /**
     * @var \DateTime
     */
    protected $date_runnable;

    /**
     * @var string
     */
    protected $task_group;

    /**
     * @var string
     */
    protected $status = 'queued';

    /**
     * @var \DateTime
     */
    protected $date_started;

    /**
     * @var \DateTime
     */
    protected $date_completed;

    /**
     * @var string
     */
    protected $error_text = '';

    /**
     * @var string
     */
    protected $run_status = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date_runnable = new \DateTime();
    }

    /**
     * @return string
     */
    public function getRunStatus()
    {
        return $this->run_status;
    }

    /**
     * @param string $run_status
     *
     * @return $this
     */
    public function setRunStatus($run_status)
    {
        $this->setModelField('run_status', $run_status);

        return $this;
    }

    /**
     * @return string
     */
    public function getRunnerClass()
    {
        return $this->runner_class;
    }

    /**
     * @return array
     */
    public function getTaskData()
    {
        return $this->task_data;
    }

    /**
     * @param array $task_data
     *
     * @return $this
     */
    public function setTaskData($task_data)
    {
        $this->setModelField('task_data', $task_data);

        return $this;
    }

    /**
     * @param Logger|null $logger
     *
     * @return \Application\DeskPRO\TaskQueueJob\AbstractJob
     */
    public function getRunner(Logger $logger = null)
    {
        return new $this->runner_class($this->task_data, $this->id, $logger);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getRunner()->getTitle();
    }

    /**
     * @param int    $max_time
     * @param Logger $logger
     *
     * @throws \Exception
     *
     * @return int
     */
    public function runTask($max_time = 15, Logger $logger = null)
    {
        if ($this->status === 'completed') {
            throw new \Exception('Task has already been completed');
        }

        if (!$this->date_started) {
            $this->setModelField('date_started', new \DateTime());
        }

        $this->setModelField('status', 'running');

        try {
            $runner = $this->getRunner($logger);
            $result = $runner->run($max_time);

            if ($result === AbstractJob::TASK_COMPLETED) {
                $this->setModelField('status', 'completed');
                $this->setModelField('date_completed', new \DateTime());
            } elseif ($result === AbstractJob::TASK_CONTINUING) {
                $this->setModelField('task_data', $runner->getData());
            } else {
                throw new \Exception('Unexpected return value from task; expected TASK_COMPLETED or TASK_CONTINUING');
            }

            return $result;
        } catch (\Exception $e) {
            $this->setModelField('status', 'errored');
            $this->setModelField('error_text', $e->getMessage());

            throw $e;
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TaskQueue';
        $metadata->setPrimaryTable([
            'name' => 'task_queue',
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'runner_class',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'runner_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'task_data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'task_data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_runnable',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_runnable',
        ]);
        $metadata->mapField([
            'fieldName'  => 'task_group',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'task_group',
        ]);
        $metadata->mapField([
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 25,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_started',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_started',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_completed',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_completed',
        ]);
        $metadata->mapField([
            'fieldName'  => 'error_text',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'error_text',
        ]);
        $metadata->mapField([
            'fieldName'  => 'run_status',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'run_status',
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
