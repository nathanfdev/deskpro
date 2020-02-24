<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class ContentTemplateVoter.
 */
class ContentTemplateVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ContentTemplate::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('agent_publish.use')) {
            return false;
        }

        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
                return $user->hasPerm('agent_publish.create');
            case PermissionGroupVoter::MODIFY:
                return $user->hasPerm('agent_publish.edit');
            case PermissionGroupVoter::DELETE:
                return $user->hasPerm('agent_publish.delete');
            case PermissionGroupVoter::VIEW_LIST:
            case PermissionGroupVoter::VIEW:
                return true;
        }

        return false;
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
