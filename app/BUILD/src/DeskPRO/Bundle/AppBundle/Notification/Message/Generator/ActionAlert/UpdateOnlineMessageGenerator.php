<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;

/**
 * Class UpdateOnlineMessageGenerator.
 */
class UpdateOnlineMessageGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var UpdateOnlineEvent $event */
        $messages = [];

        $actionAlert = new ActionAlert(NotificationService::TARGET_BROADCAST, $event->getAgentsOnlineStatus(), $event->getName());
        $actionAlert->setBroadcast();
        $messages[] = $actionAlert;

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof UpdateOnlineEvent) {
            return true;
        }

        return false;
    }
}
