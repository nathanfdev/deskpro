<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

interface NotifyHandlerInterface
{
    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function processEvent(SystemEventInterface $event);

    /**
     * @return string
     */
    public function getType();
}
