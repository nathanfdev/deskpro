<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class FeedbackApproved extends EmailBaseType
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
     * A link to the feedback.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $feedbackLink;

    protected $templateFile = 'emails_user:feedback_approved.html.twig';

    /**
     * FeedbackApproved constructor.
     *
     * @param Feedback $feedback
     * @param Person   $agent
     * @param string   $feedbackLink
     */
    public function __construct(Feedback $feedback, Person $agent, $feedbackLink)
    {
        $this->feedback     = $feedback;
        $this->agent        = $agent;
        $this->feedbackLink = $feedbackLink;
    }
}
