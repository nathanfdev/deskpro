<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

class TicketData implements DataInterface
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
        $data                = [];
        $data['ticket']      = $this->ticket->toApiData(true, true);
        $data['ticket_slas'] = App::getDb()->fetchAssoc('SELECT * FROM ticket_slas WHERE ticket_id = ?', [$this->ticket->id]);

        return $data;
    }
}
