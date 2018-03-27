<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Task;
use JMS\Serializer\Annotation as JMS;

class AgentTaskDueReminder extends EmailBaseType
{
    /**
     * Email recipient.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Task")
     *
     * @var Task
     */
    protected $task;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:task_due_reminder.html.twig';

    /**
     * AgentTaskDueReminder constructor.
     *
     * @param Task $task
     * @param $loginLink
     */
    public function __construct(Task $task, $loginLink)
    {
        $this->task      = $task;
        $this->loginLink = $loginLink;
    }
}
