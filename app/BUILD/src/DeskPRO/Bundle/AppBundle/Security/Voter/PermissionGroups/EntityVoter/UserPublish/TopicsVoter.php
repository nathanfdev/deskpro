<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class TopicsVoter.
 */
class TopicsVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Topic::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('guides.use')) {
            return false;
        }

        /** @var Topic $topic */
        $topic = $context->getParent();
        if ($topic) {
            if (!$this->getUserPublishChecker($user)->canViewTopic($topic)) {
                return false;
            }
            if (!$this->checkModify($attribute, $user, $topic)) {
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
