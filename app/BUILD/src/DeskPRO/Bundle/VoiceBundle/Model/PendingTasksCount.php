<?php

namespace DeskPRO\Bundle\VoiceBundle\Model;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PendingTasksCount.
 */
class PendingTasksCount
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $numPendingVoiceTasks = 0;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $numPendingChatTasks = 0;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $lastPendingVoiceTask;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $lastPendingChatTask;

    /**
     * Constructor.
     *
     * @param Task[]    $tasks
     * @param \DateTime $lastPendingVoiceTask
     * @param \DateTime $lastPendingChatTask
     */
    public function __construct(array $tasks, \DateTime $lastPendingVoiceTask = null, \DateTime $lastPendingChatTask = null)
    {
        foreach ($tasks as $task) {
            if ($task->getChannel() === VoiceWorkflow::getChannelName()) {
                ++$this->numPendingVoiceTasks;
            } elseif ($task->getChannel() === ChatWorkflow::getChannelName()) {
                ++$this->numPendingChatTasks;
            }
        }

        $this->lastPendingVoiceTask = $lastPendingVoiceTask;
        $this->lastPendingChatTask  = $lastPendingChatTask;
    }

    /**
     * @return int
     */
    public function getNumPendingVoiceTasks()
    {
        return $this->numPendingVoiceTasks;
    }

    /**
     * @return int
     */
    public function getNumPendingChatTasks()
    {
        return $this->numPendingChatTasks;
    }

    /**
     * @return \DateTime
     */
    public function getLastPendingVoiceTask()
    {
        return $this->lastPendingVoiceTask;
    }

    /**
     * @return \DateTime
     */
    public function getLastPendingChatTask()
    {
        return $this->lastPendingChatTask;
    }
}
