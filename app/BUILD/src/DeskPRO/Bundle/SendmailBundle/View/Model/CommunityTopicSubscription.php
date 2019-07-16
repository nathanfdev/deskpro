<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicSubscription extends UserEmailBaseType
{
    /**
     * The updated topics.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Community\CommunityTopic>")
     *
     * @var CommunityTopic[]
     */
    protected $updatedTopics;

    /**
     * Link to unsubscribe to community topics.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $unsubscribeUrl;

    protected $templateFile = 'emails_user:community_topics_subscription.html.twig';

    /**
     * CommunityTopicSubscription constructor.
     *
     * @param string           $portalHome
     * @param string           $unsubscribeUrl
     * @param CommunityTopic[] $updatedTopics
     */
    public function __construct($portalHome, $unsubscribeUrl, $updatedTopics)
    {
        parent::__construct($portalHome);

        $this->updatedTopics  = $updatedTopics;
        $this->unsubscribeUrl = $unsubscribeUrl;
    }
}
