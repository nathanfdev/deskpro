<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UserRules;

use Application\DeskPRO\Entity\UserRule;
use Doctrine\ORM\EntityManager;

class UserRuleEdit
{
    /**
     * @var \Application\DeskPRO\Entity\UserRule
     */
    public $user_rule;

    public function __construct(UserRule $user_rule)
    {
        $this->user_rule = $user_rule;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->user_rule);
        $em->flush();
    }
}
