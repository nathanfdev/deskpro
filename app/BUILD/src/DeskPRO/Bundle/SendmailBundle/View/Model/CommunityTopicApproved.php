<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicApproved extends EmailBaseType
{
    /**
     * The community topic has been approved.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * The agent that approved the community topic.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $agent;

    /**
     * A link to the community topic.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $communityTopicLink;

    protected $templateFile = 'emails_user:community_topic_approved.html.twig';

    /**
     * CommunityTopicApproved constructor.
     *
     * @param CommunityTopic $topic
     * @param Person         $agent
     * @param string         $communityTopicLink
     */
    public function __construct(CommunityTopic $topic, Person $agent, $communityTopicLink)
    {
        $this->topic              = $topic;
        $this->agent              = $agent;
        $this->communityTopicLink = $communityTopicLink;
    }
}
