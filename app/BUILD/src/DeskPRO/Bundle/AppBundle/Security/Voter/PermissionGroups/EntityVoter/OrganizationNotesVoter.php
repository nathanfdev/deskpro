<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class OrganizationNotesVoter.
 */
class OrganizationNotesVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return OrganizationNote::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $user->hasPerm('agent_org.notes');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
