<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
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

        if ($event instanceof AgentStatusChangedEvent) {
            $actionAlert = new ActionAlert(NotificationService::TARGET_USER_BROADCAST,
                [
                    'online'   => $event->getOnline(),
                    'agent_id' => $event->getPersonId(),
                ],
                $event->getName());
            $actionAlert->setBroadcast();
            $messages[] = $actionAlert;
        } else {
            $actionAlert = new ActionAlert(NotificationService::TARGET_USER_BROADCAST, $event->getAgentIds(), $event->getName());
            $actionAlert->setBroadcast();
            $messages[] = $actionAlert;
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof UserChatAgentsOnlineEvent || $event instanceof AgentStatusChangedEvent) {
            return true;
        }

        return false;
    }
}
