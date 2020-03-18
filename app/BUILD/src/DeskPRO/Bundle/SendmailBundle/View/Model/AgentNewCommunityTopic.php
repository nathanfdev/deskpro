<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewCommunityTopic extends EmailBaseType
{
    use EventCodeEmailBaseType;

    /**
     * The community topic.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic")
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * The person posted the topic.
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

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return 'community';
    }
}
