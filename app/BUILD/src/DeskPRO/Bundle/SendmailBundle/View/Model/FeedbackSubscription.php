<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback;
use JMS\Serializer\Annotation as JMS;

class FeedbackSubscription extends UserEmailBaseType
{
    /**
     * The updated feedback.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback>")
     *
     * @var Feedback[]
     */
    protected $updatedFeedback;

    /**
     * Link to unsubscribe to feedback items.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $unsubscribeUrl;

    protected $templateFile = 'emails_user:feedback_subscription.html.twig';

    /**
     * FeedbackSubscription constructor.
     *
     * @param string $portalHome
     * @param $unsubscribeUrl
     * @param Feedback[] $updatedFeedback
     */
    public function __construct($portalHome, $unsubscribeUrl, $updatedFeedback)
    {
        parent::__construct($portalHome);

        $this->updatedFeedback = $updatedFeedback;
        $this->unsubscribeUrl  = $unsubscribeUrl;
    }
}
