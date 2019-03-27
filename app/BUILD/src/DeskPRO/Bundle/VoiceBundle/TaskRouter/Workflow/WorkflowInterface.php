<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;

/**
 * Interface WorkflowInterface.
 */
interface WorkflowInterface
{
    /**
     * @return string
     */
    public static function getChannelName();

    /**
     * @param Task $task
     *
     * @return bool
     */
    public function isTaskTimedOut(Task $task);

    /**
     * @param Task $task
     * @param bool $ignoreRejected
     *
     * @return Worker[]
     */
    public function getAvailableWorkers(Task $task, $ignoreRejected = false);

    /**
     * @param Task     $task
     * @param Worker[] $workers
     */
    public function assignTask(Task $task, array $workers);
}
