<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use JMS\Serializer\Annotation as JMS;

class FeedbackUpdated extends EmailBaseType
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
     * A link to the feedback.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $feedbackLink;

    protected $templateFile = 'emails_user:feedback_updated.html.twig';

    public function __construct(Feedback $feedback, $feedbackLink)
    {
        $this->feedback     = $feedback;
        $this->feedbackLink = $feedbackLink;
    }
}
