<?php

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
    const CHANNEL_CHAT_NEW               = 'chat.new';
    const CHANNEL_CHAT_DEPARTMENT_CHANGE = 'chat.depchange';
    const CHANNEL_CHAT_REASSIGNED        = 'chat.reassigned';
    const CHANNEL_CHAT_UNASSIGNED        = 'chat.unassigned';
    const CHANNEL_CHAT_ENDED             = 'chat.ended';

    const SEND = 'client_message.send';

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
