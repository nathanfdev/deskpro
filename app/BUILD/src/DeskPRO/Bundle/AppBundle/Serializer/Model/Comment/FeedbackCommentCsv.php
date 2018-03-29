<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\FeedbackComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class FeedbackCommentCsv extends FeedbackComment
{
    /**
     * Name of comment's author.
     *
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * ID of feedback this comment belongs to.
     *
     * @JMS\Type("integer")
     */
    private $feedbackId;

    /**
     * Title of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * Content of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $feedbackContent;

    /**
     * Status of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $feedbackStatus;

    /**
     * Hidden status of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $hiddenStatus;

    /**
     * Category of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     */
    public function __construct($entity)
    {
        parent::__construct($entity);
        $this->person          = $entity->getPerson() ? $entity->getPerson()->getName() : '';
        $this->content         = mb_substr($entity->getContent(), 0, 50);
        $this->feedbackId      = $this->feedback->getId();
        $this->title           = $this->feedback->getTitle();
        $this->feedbackContent = mb_substr($this->feedback->getRealContent(), 0, 50);
        $this->feedbackStatus  = $this->feedback->getStatusCategory() ? $this->feedback->getStatusCategory()
            ->getTitle() : '';
        $this->hiddenStatus = $this->feedback->getHiddenStatus() ?: '';
        $this->category     = $this->feedback->getCategory() ? $this->feedback->getCategory()->getTitle() : '';
    }
}
