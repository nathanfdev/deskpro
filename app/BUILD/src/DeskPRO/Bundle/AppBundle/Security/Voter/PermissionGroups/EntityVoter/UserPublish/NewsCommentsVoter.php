<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class NewsCommentsVoter.
 */
class NewsCommentsVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return NewsComment::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        return $user->hasPerm('news.use');
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
