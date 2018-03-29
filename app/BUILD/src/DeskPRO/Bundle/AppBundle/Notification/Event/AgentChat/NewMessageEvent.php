<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat;

/**
 * Class NewMessageEvent.
 */
class NewMessageEvent extends AbstractMessageEvent
{
    const EVENT_NAME = 'notification.agent_chat.new_message';
}
