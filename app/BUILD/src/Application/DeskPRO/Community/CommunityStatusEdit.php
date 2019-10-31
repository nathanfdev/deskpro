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
    public $community_status;

    public function __construct(CommunityTopicStatusCategory $communityStatus)
    {
        $this->community_status = $communityStatus;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->community_status);
        $em->flush();
    }
}
