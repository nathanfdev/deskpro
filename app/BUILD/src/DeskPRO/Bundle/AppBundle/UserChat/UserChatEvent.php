<?php

namespace DeskPRO\Bundle\AppBundle\UserChat;

use Application\DeskPRO\Entity\ChatConversation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserChatEvent.
 */
class UserChatEvent extends Event
{
    const VALIDATE_EMAIL = 'user_chat.validate_email';
    const STARTED        = 'user_chat.message_started';
    const USER_LEFT      = 'user_chat.message_user-left';
    const USER_RETURNED  = 'user_chat.message_user-returned';
    const USER_JOINED    = 'user_chat.message_user-joined';
    const SET_DEPARTMENT = 'user_chat.message_set-department';
    const ASSIGNED       = 'user_chat.message_assigned';
    const UNASSIGNED     = 'user_chat.message_unassigned';
    const USER_TRACK     = 'user_chat.msg_new_user_track';
    const AGENT_TIMEOUT  = 'user_chat.message_agent-timeout';
    const USER_TIMEOUT   = 'user_chat.message_user-timeout';
    const WAIT_TIMEOUT   = 'user_chat.message_wait-timeout';
    const END_BY_USER    = 'user_chat.message_ended-by-user';
    const END_BY         = 'user_chat.message_ended-by';
    const ENDED          = 'user_chat.message_ended';
    const SEND_MESSAGE   = 'user_chat.send_message';
    const ACK_MESSAGES   = 'user_chat.ack_messages';
    const USER_TYPING    = 'user_chat.user_typing';
    const POLLING        = 'user_chat.polling';
    const FIND_AGENT     = 'user_chat.find_agent';

    /**
     * @var ChatConversation
     */
    protected $chat;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * Constructor.
     *
     * @param ChatConversation $chat
     * @param mixed            $data
     * @param array            $metadata
     */
    public function __construct(ChatConversation $chat, $data = [], array $metadata = [])
    {
        $this->chat     = $chat;
        $this->data     = $data;
        $this->metadata = $metadata;
    }

    /**
     * @return ChatConversation
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
