<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicDisapproved extends EmailBaseType
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
     * The agent that disapproved the topic.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $agent;

    /**
     * The reason why the community topic was not approved.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $reason;

    protected $templateFile = 'emails_user:community_topic_disapproved.html.twig';

    /**
     * CommunityTopicDisapproved constructor.
     *
     * @param CommunityTopic $topic
     * @param Person         $agent
     * @param string         $reason
     */
    public function __construct(CommunityTopic $topic, Person $agent, $reason)
    {
        $this->topic  = $topic;
        $this->agent  = $agent;
        $this->reason = $reason;
    }
}
