<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Messenger;

class ChatMessageEvent extends ChatEvent
{
    const EVENT_NAME = 'messenger.chat.message';

    const CHAT_MESSAGE_EVENT_TYPE = 'chat.message';

    /**
     * @var int
     */
    protected $messageId;

    /**
     * ChatMessageEvent constructor.
     *
     * @param int    $chatId
     * @param int    $messageId
     * @param string $type
     * @param array  $data
     */
    public function __construct($chatId, $messageId, $type, $data = [])
    {
        $this->messageId = $messageId;
        parent::__construct($chatId, $type, $data);
    }

    /**
     * @return int
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    public function __sleep()
    {
        return [
            'chatId'    => $this->chatId,
            'messageId' => $this->messageId,
            'type'      => $this->type,
            'data'      => $this->data,
        ];
    }
}
