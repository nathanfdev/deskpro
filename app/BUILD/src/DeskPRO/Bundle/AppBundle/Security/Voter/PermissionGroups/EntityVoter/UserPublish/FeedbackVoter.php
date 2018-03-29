<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class FeedbackVoter.
 */
class FeedbackVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Feedback::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('feedback.use')) {
            return false;
        }

        /** @var Feedback $feedback */
        $feedback = $context->getParent();
        if ($feedback) {
            if (!$this->getUserPublishChecker($user)->canViewFeedback($feedback)) {
                return false;
            }
            if (!$this->checkModify($attribute, $user, $feedback)) {
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
