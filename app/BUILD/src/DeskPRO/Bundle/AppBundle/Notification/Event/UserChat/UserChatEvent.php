<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\UserChat;

use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

class UserChatEvent extends LegacySystemEvent
{
    const EVENT_NAME = 'legacy.user.chat';
}
