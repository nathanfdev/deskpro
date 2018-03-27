<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PermissionChecker\PublishChecker;
use Application\DeskPRO\People\PermissionChecker\UserPublishChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class AbstractUserPublishVoter.
 */
abstract class AbstractUserPublishVoter implements PermissionGroupEntityVoterInterface
{
    const VALIDATE = 'validate';

    /**
     * @param Person $user
     *
     * @return UserPublishChecker
     */
    protected function getUserPublishChecker(Person $user)
    {
        return $user->PermissionsManager->UserPublishChecker;
    }

    /**
     * @param Person $user
     *
     * @return PublishChecker
     */
    protected function getPublishChecker(Person $user)
    {
        return $user->PermissionsManager->PublishChecker;
    }

    /**
     * @param string          $attribute
     * @param Person          $user
     * @param ContentAbstract $content
     *
     * @return bool
     */
    protected function checkModify($attribute, Person $user, ContentAbstract $content)
    {
        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
            case PermissionGroupVoter::MODIFY:
                return $this->getPublishChecker($user)->canEdit($content);
            case PermissionGroupVoter::DELETE:
                return $this->getPublishChecker($user)->canDelete($content);
            case self::VALIDATE:
                return $this->getPublishChecker($user)->canValidate($content);
        }

        return true;
    }
}
