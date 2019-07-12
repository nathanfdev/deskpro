<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicComment extends CommentAbstract
{
    /**
     * CommunityTopic this comment belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CommunityTopic>")
     * @JMS\Groups({"list", "details"})
     *
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     */
    public function __construct($entity)
    {
        parent::__construct($entity);
        $this->topic = $entity->getTopic();
    }
}
