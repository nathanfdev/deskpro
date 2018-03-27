<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Task;
use JMS\Serializer\Annotation as JMS;

class AgentTaskAssigned extends EmailBaseType
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
     * Person that perform the password reset.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $performer;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:task_assigned.html.twig';

    /**
     * AgentTaskAssigned constructor.
     *
     * @param Task   $task
     * @param Person $performer
     * @param $loginLink
     */
    public function __construct(Task $task, Person $performer, $loginLink)
    {
        $this->task      = $task;
        $this->performer = $performer;
        $this->loginLink = $loginLink;
    }
}
