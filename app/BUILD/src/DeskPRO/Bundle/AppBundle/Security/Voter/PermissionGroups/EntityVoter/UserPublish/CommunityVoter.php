<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class CommunityVoter.
 */
class CommunityVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return CommunityTopic::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('agent_publish.use')) {
            return false;
        }

        /** @var CommunityTopic $communityTopic */
        $communityTopic = $context->getParent();
        if ($communityTopic) {
            if (!$this->checkModify($attribute, $user, $communityTopic)) {
                return false;
            }
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
