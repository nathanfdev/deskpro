<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use JMS\Serializer\Annotation as JMS;

class FeedbackCreatedForUser extends EmailBaseType
{
    /**
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
     *
     * @var Feedback
     */
    protected $feedback;

    protected $templateFile = 'emails_user:new-feedback-created-for-user.html.twig';

    public function __construct(Feedback $feedback)
    {
        $this->feedback     = $feedback;
    }
}
