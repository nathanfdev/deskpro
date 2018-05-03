<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use JMS\Serializer\Annotation as JMS;

class FeedbackNew extends EmailBaseType
{
    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
     *
     * @var Feedback
     */
    protected $feedback;

    protected $templateFile = 'emails_user:feedback_new.html.twig';

    /**
     * FeedbackNew constructor.
     *
     * @param Feedback $feedback
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }
}
