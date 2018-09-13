<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;

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
     */
    public function assignTask(Task $task);
}
