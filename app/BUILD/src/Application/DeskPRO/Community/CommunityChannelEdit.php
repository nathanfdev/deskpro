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
    public $communityChannel;

    public function __construct(CommunityChannel $communityChannel)
    {
        $this->communityChannel = $communityChannel;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->communityChannel);
        $em->flush();
    }
}
