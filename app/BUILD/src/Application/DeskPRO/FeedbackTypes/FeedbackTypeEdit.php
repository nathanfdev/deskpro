<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackTypes;

use Application\DeskPRO\Entity\FeedbackCategory;
use Doctrine\ORM\EntityManager;

class FeedbackTypeEdit
{
    /**
     * @var \Application\DeskPRO\Entity\FeedbackCategory
     */
    public $feedback_type;

    public function __construct(FeedbackCategory $feedback_type)
    {
        $this->feedback_type = $feedback_type;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->feedback_type);
        $em->flush();
    }
}
