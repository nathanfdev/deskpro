<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Event;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

class ChatEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'messenger.chat';

    const CHAT_STARTED_EVENT_TYPE        = 'chat.started';
    const CHAT_ENDED_EVENT_TYPE          = 'chat.ended';
    const CHAT_USER_TIMEOUT_EVENT_TYPE   = 'chat.userTimeout';
    const CHAT_WAIT_TIMEOUT_EVENT_TYPE   = 'chat.waitTimeout';
    const CHAT_TRANSCRIPT_EVENT_TYPE     = 'chat.transcript';
    const CHAT_RATING_EVENT_TYPE         = 'chat.rating';
    const CHAT_AGENT_ASSIGNED_EVENT_TYPE = 'chat.agentAssigned';
    const CHAT_USER_JOINED_EVENT_TYPE    = 'chat.userJoined';
    const CHAT_USER_LEFT_EVENT_TYPE      = 'chat.userLeft';
    const TYPING_START_EVENT_TYPE        = 'chat.typing.start';
    const TYPING_END_EVENT_TYPE          = 'chat.typing.end';

    /**
     * @var int
     */
    protected $chatId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $data;

    /**
     * ChatEvent constructor.
     *
     * @param int    $chatId
     * @param string $type
     * @param array  $data
     */
    public function __construct($chatId, $type, $data = [])
    {
        $this->chatId = $chatId;
        $this->type   = $type;
        $this->data   = $data;
    }

    /**
     * @return int
     */
    public function getChatId()
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function __sleep()
    {
        return [
            'chatId' => $this->chatId,
            'type'   => $this->type,
            'data'   => $this->data,
        ];
    }
}
