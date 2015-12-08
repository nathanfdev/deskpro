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

/**
 * Class UserChatSystemEvent.
 */
class UserChatSystemEvent extends UserChatEvent
{
    const TYPE_STARTED        = 'message_started';
    const TYPE_USER_LEFT      = 'message_user-left';
    const TYPE_USER_RETURNED  = 'message_user-returned';
    const TYPE_USER_JOINED    = 'message_user-joined';
    const TYPE_SET_DEPARTMENT = 'message_set-department';
    const TYPE_ASSIGNED       = 'message_assigned';
    const TYPE_UNASSIGNED     = 'message_unassigned';
    const TYPE_USER_TRACK     = 'msg_new_user_track';
    const TYPE_AGENT_TIMEOUT  = 'message_agent-timeout';
    const TYPE_USER_TIMEOUT   = 'message_user-timeout';
    const TYPE_WAIT_TIMEOUT   = 'message_wait-timeout';
    const TYPE_END_BY_USER    = 'message_ended-by-user';
    const TYPE_END_BY         = 'message_ended-by';
    const TYPE_ENDED          = 'message_ended';

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param ChatConversation $conversation
     * @param string           $type
     */
    public function __construct(ChatConversation $conversation, $type)
    {
        parent::__construct($conversation);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
