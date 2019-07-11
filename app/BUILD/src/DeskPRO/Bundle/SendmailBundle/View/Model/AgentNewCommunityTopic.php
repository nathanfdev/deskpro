<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewCommunityTopic extends EmailBaseType
{
    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * The feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $person;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:new_community_topic.html.twig';

    public function __construct(CommunityTopic $topic, Person $person, $loginLink)
    {
        $this->topic     = $topic;
        $this->person    = $person;
        $this->loginLink = $loginLink;
    }
}
