<?php

namespace DeskPRO\Bundle\VoiceBundle\Permissions;

use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;

/**
 * Class UserChatPermissionsChecker.
 */
class UserChatPermissionsChecker
{
    /**
     * @param AbstractUserChatQueueTarget $target
     *
     * @return bool
     */
    public function canBeMemberOfChatQueue(AbstractUserChatQueueTarget $target)
    {
        if ($target instanceof UserChatQueueAgent) {
            return $target->getAgent()->isActiveAgent();
        }

        return true;
    }
}
