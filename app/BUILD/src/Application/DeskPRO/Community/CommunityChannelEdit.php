<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\Entity\CommunityChannel;
use Doctrine\ORM\EntityManager;

class CommunityChannelEdit
{
    /**
     * @var \Application\DeskPRO\Entity\CommunityChannel
     */
    public $community_channel;

    public function __construct(CommunityChannel $communityChannel)
    {
        $this->community_channel = $communityChannel;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->community_channel);
        $em->flush();
    }
}
