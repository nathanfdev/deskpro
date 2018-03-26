<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Interface PermissionGroupEntityInterface.
 */
interface PermissionGroupEntityVoterInterface
{
    /**
     * @return string
     */
    public static function getEntityClass();

    /**
     * @param string                 $attribute
     * @param PermissionGroupContext $context
     * @param Person                 $user
     *
     * @return mixed
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user);

    /**
     * @param string                 $attribute
     * @param PermissionGroupContext $context
     * @param Person                 $user
     *
     * @return mixed
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user);
}
