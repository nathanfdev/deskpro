<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;

/**
 * This is a legacy adapter to handle old code that needs the old ticket logger.
 *
 * @deprecated
 */
class TicketChangeTracker
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function done()
    {
        $this->ticket->_autoProcessTicket();
    }

    public function recordExtra()
    {
        // ignore
    }

    public function recordMultiPropertyChanged()
    {
        // ignore
    }

    public function setLogger()
    {
        // ignore
    }

    public function setApplyingTrigger()
    {
        // ignore
    }

    public function getApplyingTrigger()
    {
        return;
    }

    public function setApplyingSla()
    {
        // ignore
    }

    public function getApplyingSla()
    {
        return;
    }

    public function getApplyingSlaStatus()
    {
        return;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function isTriggerChangeField()
    {
        return false;
    }

    public function preDone()
    {
    }

    public function getLogMessagesAsString()
    {
        return '';
    }

    public function __call($n, $v)
    {
        throw new \BadMethodCallException();
    }
}
