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
namespace DeskPRO\Bundle\AppBundle\EventListener\ClientMessage;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ClientMessageEvent.
 */
class ClientMessageEvent extends Event
{
    const CHANNEL_CHAT_NEW                = 'chat.new';
    const CHANNEL_CHAT_DEPARTMENT_CHANGE  = 'chat.depchange';
    const CHANNEL_CHAT_REASSIGNED         = 'chat.reassigned';
    const CHANNEL_CHAT_UNASSIGNED         = 'chat.unassigned';
    const CHANNEL_CHAT_ENDED              = 'chat.ended';
    const CHANNEL_CHAT_NEW_MESSAGE        = 'newmessage';
    const CHANNEL_CHAT_NEW_MESSAGE_HIDDEN = 'newmessage_hidden';
    const CHANNEL_CHAT_USER_TYPING        = 'usertyping';
    const CHANNEL_CHAT_ACK_MESSAGES       = 'ack_messages';

    const SEND_MESSAGE = 'client_message.send';

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $created_by;

    /**
     * Constructor.
     *
     * @param string $channel
     * @param mixed  $data
     * @param string $created_by
     */
    public function __construct($channel, $data = [], $created_by = '')
    {
        $this->channel    = $channel;
        $this->data       = $data;
        $this->created_by = $created_by;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }
}
