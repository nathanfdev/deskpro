<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

/**
 * Class UserNotificationHandler.
 */
class UserNotificationHandler extends NotifyHandler
{
    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function processEvent(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->generators as $generator) {
            if ($generator->canCreateMessage($event)) {
                $messages = array_merge($messages, $generator->createMessages($event));
            }
        }

        return $messages;
    }
}
