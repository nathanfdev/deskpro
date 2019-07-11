<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicUpdated extends EmailBaseType
{
    /**
     * The feedback that has been approved.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * A link to the community topic.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $communityTopicLink;

    protected $templateFile = 'emails_user:community_topic_updated.html.twig';

    public function __construct(CommunityTopic $communityTopic, $communityTopicLink)
    {
        $this->topic              = $communityTopic;
        $this->communityTopicLink = $communityTopicLink;
    }
}
