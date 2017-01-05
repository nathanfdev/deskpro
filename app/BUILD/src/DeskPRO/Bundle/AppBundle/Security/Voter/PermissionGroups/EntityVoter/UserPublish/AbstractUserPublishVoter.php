<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
