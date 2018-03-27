<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

interface MessageGeneratorInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event);

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event);
}
