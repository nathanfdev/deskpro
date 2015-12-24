<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\UserChat;

use Application\DeskPRO\Entity\ChatConversation;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserChatEvent.
 */
class UserChatEvent extends Event
{
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
    const ACK_MESSAGES   = 'user.chat.ack_messages';
    const USER_TYPING    = 'user.chat.user_typing';

    /**
     * @var ChatConversation
     */
    protected $conversation;

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
     * @param ChatConversation $conversation
     * @param mixed            $data
     * @param array            $metadata
     */
    public function __construct(ChatConversation $conversation, $data = [], array $metadata = [])
    {
        $this->conversation = $conversation;
        $this->data         = $data;
        $this->metadata     = $metadata;
    }

    /**
     * @return ChatConversation
     */
    public function getConversation()
    {
        return $this->conversation;
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
