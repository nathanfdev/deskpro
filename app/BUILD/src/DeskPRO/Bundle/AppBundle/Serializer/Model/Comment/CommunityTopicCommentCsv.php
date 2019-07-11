<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\CommunityTopicComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class CommunityTopicCommentCsv extends CommunityTopicComment
{
    /**
     * Name of comment's author.
     *
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * ID of community topic this comment belongs to.
     *
     * @JMS\Type("integer")
     */
    private $topicId;

    /**
     * Title of community topic this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * Content of community topic this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $topicContent;

    /**
     * Status of community topic this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $communityTopicStatus;

    /**
     * Hidden status of community topic this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $hiddenStatus;

    /**
     * Channel of community topic this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $channel;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     */
    public function __construct($entity)
    {
        parent::__construct($entity);
        $this->person               = $entity->getPerson() ? $entity->getPerson()->getName() : '';
        $this->content              = mb_substr($entity->getContent(), 0, 50);
        $this->topicId              = $this->topic->getId();
        $this->title                = $this->topic->getTitle();
        $this->topicContent         = mb_substr($this->topic->getRealContent(), 0, 50);
        $this->communityTopicStatus = $this->topic->getStatusCategory() ? $this->topic->getStatusCategory()
            ->getTitle() : '';
        $this->hiddenStatus = $this->topic->getHiddenStatus() ?: '';
        $this->channel      = $this->topic->getCategory() ? $this->topic->getCategory()->getTitle() : '';
    }
}
