<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Zapier;

class TicketUpdate
{
    protected $ticket;

    protected $changes;

    protected $performer;

    /**
     * @return mixed
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param mixed $ticket
     *
     * @return TicketUpdate
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param mixed $changes
     *
     * @return TicketUpdate
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * @param mixed $performer
     *
     * @return TicketUpdate
     */
    public function setPerformer($performer)
    {
        $this->performer = $performer;

        return $this;
    }
}
