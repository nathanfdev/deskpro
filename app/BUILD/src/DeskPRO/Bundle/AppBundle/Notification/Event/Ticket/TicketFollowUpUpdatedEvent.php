<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Ticket;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class TicketFollowUpUpdatedEvent.
 */
class TicketFollowUpUpdatedEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'ticket.follow_up_updated';

    private $ticket   = null;
    private $ticketId = null;
    private $action   = null;

    /**
     * SnippetsUpdatedEvent constructor.
     *
     * @param Ticket $ticket
     * @param string $action
     */
    public function __construct($ticket, $action = null)
    {
        $this->ticket = $ticket;
        if ($ticket) {
            $this->ticketId = $ticket->getId();
        }
        $this->action = $action;
    }

    /**
     * @return Ticket|null
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        return ['ticketId', 'action'];
    }
}
