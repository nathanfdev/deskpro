<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

class NewsCategoriesVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return NewsCategory::class;
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
