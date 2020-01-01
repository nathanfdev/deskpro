<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Notification\Event\People\UserChatAgentsOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;

/**
 * Class UserChatAgentsOnlineGenerator.
 */
class UserChatAgentsOnlineGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     *
     * @var UserChatAgentsOnlineEvent
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var UserChatAgentsOnlineEvent $event */
        $messages = [];

        $actionAlert = new ActionAlert(NotificationService::TARGET_USER_BROADCAST, $event->getAgentIds(), $event->getName());
        $actionAlert->setBroadcast();
        $messages[] = $actionAlert;

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof UserChatAgentsOnlineEvent) {
            return true;
        }

        return false;
    }
}
