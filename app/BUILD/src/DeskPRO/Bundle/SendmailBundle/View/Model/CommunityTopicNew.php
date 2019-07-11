<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicNew extends EmailBaseType
{
    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    protected $templateFile = 'emails_user:community_topic_new.html.twig';

    /**
     * FeedbackNew constructor.
     *
     * @param CommunityTopic $communityTopic
     */
    public function __construct(CommunityTopic $communityTopic)
    {
        $this->topic = $communityTopic;
    }
}
