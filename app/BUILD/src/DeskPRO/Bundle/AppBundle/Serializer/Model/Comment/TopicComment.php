<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\TopicComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class TopicComment extends CommentAbstract
{
    /**
     * Person`s avatar.
     *
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $avatar = null;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     * @param string        $avatar
     */
    public function __construct($entity, $avatar)
    {
        parent::__construct($entity);
        if ($this->person) {
            if (!$this->name) {
                $this->name = $this->person->getName();
            }
            $this->avatar = $avatar;
        }
    }
}
