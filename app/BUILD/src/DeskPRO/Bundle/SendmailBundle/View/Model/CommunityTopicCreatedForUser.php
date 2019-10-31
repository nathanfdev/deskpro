<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicCreatedForUser extends EmailBaseType
{
    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    protected $templateFile = 'emails_user:new-community-topic-created-for-user.html.twig';

    public function __construct(CommunityTopic $communityTopic)
    {
        $this->topic = $communityTopic;
    }
}
