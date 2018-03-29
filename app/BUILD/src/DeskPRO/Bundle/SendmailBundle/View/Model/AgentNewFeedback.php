<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewFeedback extends EmailBaseType
{
    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $person;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:new_feedback.html.twig';

    public function __construct(Feedback $feedback, Person $person, $loginLink)
    {
        $this->feedback  = $feedback;
        $this->person    = $person;
        $this->loginLink = $loginLink;
    }
}
