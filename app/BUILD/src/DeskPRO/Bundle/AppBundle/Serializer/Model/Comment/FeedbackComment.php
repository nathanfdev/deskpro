<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class FeedbackComment extends CommentAbstract
{
    /**
     * Feedback this comment belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Feedback>")
     * @JMS\Groups({"list", "details"})
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     */
    public function __construct($entity)
    {
        parent::__construct($entity);
        $this->feedback = $entity->getFeedback();
    }
}
