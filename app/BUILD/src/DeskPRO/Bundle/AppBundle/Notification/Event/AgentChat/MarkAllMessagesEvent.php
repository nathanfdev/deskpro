<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
