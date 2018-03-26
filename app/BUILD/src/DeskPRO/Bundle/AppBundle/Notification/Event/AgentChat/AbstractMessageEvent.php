<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class AbstractMessageEvent.
 */
abstract class AbstractMessageEvent extends AbstractSystemEvent
{
    /** @var int */
    protected $message_id;

    /**
     * @param int $message_id
     */
    public function __construct($message_id)
    {
        $this->message_id = $message_id;
    }

    /**
     * @return int
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * @param int $message_id
     *
     * @return NewMessageEvent
     */
    public function setMessageId($message_id)
    {
        $this->message_id = $message_id;

        return $this;
    }

    public function __sleep()
    {
        return ['message_id'];
    }
}
