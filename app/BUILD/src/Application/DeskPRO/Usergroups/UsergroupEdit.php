<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usergroups;

use Application\DeskPRO\Entity\Usergroup;
use Doctrine\ORM\EntityManager;

class UsergroupEdit
{
    /**
     * @var \Application\DeskPRO\Entity\Usergroup
     */
    public $group;

    public function __construct(Usergroup $user_group)
    {
        $this->group = $user_group;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->group);
        $em->flush();
    }
}
