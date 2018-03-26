<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class NewsVoter.
 */
class NewsVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return News::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('news.use')) {
            return false;
        }

        /** @var News $news */
        $news = $context->getParent();
        if ($news) {
            if (!$this->getUserPublishChecker($user)->canViewNews($news)) {
                return false;
            }
            if (!$this->checkModify($attribute, $user, $news)) {
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
