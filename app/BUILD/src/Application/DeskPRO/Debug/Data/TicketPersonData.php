<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\Entity\Ticket;

class TicketPersonData implements DataInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getData()
    {
        $data           = [];
        $data['person'] = $this->ticket->person->toApiData(true, true);

        return $data;
    }
}
