<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Ticket;

use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

/**
 * Class TicketUpdatedEvent.
 */
class TicketUpdatedEvent extends LegacySystemEvent
{
    const EVENT_NAME = 'legacy.ticket.updated';

    /** @var array */
    protected $data;

    /**
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, array $data)
    {
        parent::__construct($type, $data);
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->data['ticket_id'];
    }

    /**
     * @param int $ticketId
     *
     * @return TicketUpdatedEvent
     */
    public function setTicketId($ticketId)
    {
        $this->data['ticket_id'] = $ticketId;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return TicketUpdatedEvent
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'ticket_id',
            'data',
        ];
    }
}
