<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class PeopleVoter.
 */
class PeopleVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Person::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        $peopleUse = $user->hasPerm('agent_people.use');

        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
                return $peopleUse && $user->hasPerm('agent_people.create');
            case PermissionGroupVoter::MODIFY:
                return $peopleUse && $user->hasPerm('agent_people.edit');
            case PermissionGroupVoter::DELETE:
                return $peopleUse && $user->hasPerm('agent_people.delete');
            case PermissionGroupVoter::VIEW_LIST:
            case PermissionGroupVoter::VIEW:
                return $peopleUse || ($user->getAgentData() && $user->getAgentData()->isVoiceEnabled());
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
