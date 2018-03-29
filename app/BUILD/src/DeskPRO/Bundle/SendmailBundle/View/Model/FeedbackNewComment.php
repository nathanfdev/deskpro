<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\FeedbackComment;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use JMS\Serializer\Annotation as JMS;

class FeedbackNewComment extends EmailBaseType
{
    /**
     * The new comment.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\FeedbackComment")
     *
     * @var FeedbackComment
     */
    protected $comment;

    /**
     * The feedback.
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

    protected $templateFile = 'emails_user:feedback_new_comment.html.twig';

    /**
     * FeedbackNewComment constructor.
     *
     * @param FeedbackComment $comment
     * @param Feedback        $feedback
     * @param $feedbackLink
     */
    public function __construct(FeedbackComment $comment, Feedback $feedback, $feedbackLink)
    {
        $this->comment      = $comment;
        $this->feedback     = $feedback;
        $this->feedbackLink = $feedbackLink;
    }
}
