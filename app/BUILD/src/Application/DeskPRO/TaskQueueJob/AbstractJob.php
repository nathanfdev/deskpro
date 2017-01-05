<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
