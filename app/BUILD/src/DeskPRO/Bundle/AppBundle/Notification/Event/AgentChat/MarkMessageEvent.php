<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat;

/**
 * Class MarkMessageEvent.
 */
class MarkMessageEvent extends AbstractMessageEvent
{
    const EVENT_NAME = 'notification.agent_chat.mark_message';

    /**
     * @var int
     */
    protected $status;

    /**
     * @param int $message_id
     * @param int $status
     */
    public function __construct($message_id, $status)
    {
        parent::__construct($message_id);
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function __sleep()
    {
        return array_merge(parent::__sleep(), ['status']);
    }
}
