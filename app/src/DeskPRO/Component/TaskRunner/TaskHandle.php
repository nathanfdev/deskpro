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

namespace DeskPRO\Component\TaskRunner;

use DeskPRO\Component\TaskRunner\Task\TaskInterface;

/**
 * Once a task is started, the processor should return a handle
 * that can be used to interact with the task later (e.g., check its state, terminate it, etc).
 */
class TaskHandle
{
    /**
     * @var TaskInterface
     */
    private $task;

    /**
     * @var mixed
     */
    private $handle;

    /**
     * @var float
     */
    private $start_time;

    /**
     * @param TaskInterface $task
     * @param mixed         $handle
     */
    public function __construct(TaskInterface $task, $handle)
    {
        $this->task       = $task;
        $this->handle     = $handle;
        $this->start_time = microtime(true);
    }

    /**
     * @return TaskInterface
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return float
     */
    public function getStartTime()
    {
        return $this->start_time;
    }
}
