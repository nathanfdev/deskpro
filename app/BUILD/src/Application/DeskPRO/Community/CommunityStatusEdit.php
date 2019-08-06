<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Doctrine\ORM\EntityManager;

class CommunityStatusEdit
{
    /**
     * @var CommunityTopicStatusCategory
     */
    public $communityStatus;

    public function __construct(CommunityTopicStatusCategory $communityStatus)
    {
        $this->communityStatus = $communityStatus;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->communityStatus);
        $em->flush();
    }
}
