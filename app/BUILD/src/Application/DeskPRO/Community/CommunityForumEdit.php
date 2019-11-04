<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityForum;
use Doctrine\ORM\EntityManager;

class CommunityForumEdit
{
    /**
     * @var \Application\DeskPRO\Entity\CommunityForum
     */
    public $community_forum;

    public function __construct(CommunityForum $communityForum)
    {
        $this->community_forum = $communityForum;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->community_forum);
        $this->community_forum->getTopicStatuses()->map(function ($junc) use ($em) {
            $em->persist($junc);
        });
        $em->flush();
    }
}
