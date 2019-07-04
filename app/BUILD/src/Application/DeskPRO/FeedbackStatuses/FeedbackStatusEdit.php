<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackStatuses;

use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Doctrine\ORM\EntityManager;

class FeedbackStatusEdit
{
    /**
     * @var \Application\DeskPRO\Entity\CommunityTopicStatusCategory
     */
    public $feedback_status;

    public function __construct(CommunityTopicStatusCategory $feedback_status)
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
