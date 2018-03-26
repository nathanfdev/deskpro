<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class FeedbackDisapproved extends EmailBaseType
{
    /**
     * The feedback that has been approved.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * The agent that approved the feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $agent;

    /**
     * The reason why the feedback was not approved.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $reason;

    protected $templateFile = 'emails_user:feedback_disapproved.html.twig';

    /**
     * FeedbackDisapproved constructor.
     *
     * @param Feedback $feedback
     * @param Person   $agent
     * @param $reason
     */
    public function __construct(Feedback $feedback, Person $agent, $reason)
    {
        $this->feedback = $feedback;
        $this->agent    = $agent;
        $this->reason   = $reason;
    }
}
