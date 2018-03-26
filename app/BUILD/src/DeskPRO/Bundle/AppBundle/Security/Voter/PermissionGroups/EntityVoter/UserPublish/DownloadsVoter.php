<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class DownloadsVoter.
 */
class DownloadsVoter extends AbstractUserPublishVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Download::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('downloads.use')) {
            return false;
        }

        /** @var Download $download */
        $download = $context->getParent();
        if ($download) {
            if (!$this->getUserPublishChecker($user)->canViewDownload($download)) {
                return false;
            }
            if (!$this->checkModify($attribute, $user, $download)) {
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
