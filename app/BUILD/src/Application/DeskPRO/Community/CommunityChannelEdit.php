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
    public $feedback_type;

    public function __construct(CommunityChannel $feedback_type)
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
