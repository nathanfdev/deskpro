<?php

/**
 * DeskPRO.
 *
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TaskQueue;
use Application\DeskPRO\Log\Logger;

/**
 * Class AbstractJob.
 */
abstract class AbstractJob
{
    const TASK_COMPLETED  = 1;
    const TASK_CONTINUING = 2;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var TaskQueue
     */
    protected $taskId;

    /**
     * @var Logger|null
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param array       $data
     * @param int         $taskId
     * @param Logger|null $logger
     */
    public function __construct(array $data, $taskId, Logger $logger = null)
    {
        $this->data   = array_merge($this->getDefaultData(), $data);
        $this->taskId = $taskId;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Logger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return TaskQueue
     */
    public function getTask()
    {
        return App::getOrm()->find(TaskQueue::class, $this->taskId);
    }

    /**
     * @return mixed
     */
    abstract protected function getDefaultData();

    /**
     * @param $max_time
     *
     * @return int
     */
    abstract public function run($max_time);

    /**
     * @return string
     */
    abstract public function getTitle();
}
