<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class MarkAllMessagesEvent.
 */
class MarkAllMessagesEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'notification.agent_chat.mark_all_messages';

    /**
     * @var int
     */
    protected $chatId;

    /**
     * @var int
     */
    protected $status;

    /**
     * @param int $chatId
     * @param int $status
     */
    public function __construct($chatId, $status)
    {
        $this->chatId = $chatId;
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getChatId()
    {
        return $this->chatId;
    }

    /**
     * @param int $chatId
     *
     * @return $this
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;

        return $this;
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
        return array_merge(['chat_id', 'status']);
    }
}
