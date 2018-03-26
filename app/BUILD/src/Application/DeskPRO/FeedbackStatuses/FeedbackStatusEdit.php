<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackStatuses;

use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Doctrine\ORM\EntityManager;

class FeedbackStatusEdit
{
    /**
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory
     */
    public $feedback_status;

    public function __construct(FeedbackStatusCategory $feedback_status)
    {
        $this->feedback_status = $feedback_status;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->feedback_status);
        $em->flush();
    }
}
