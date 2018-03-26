<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

class TicketLogsData implements DataInterface
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
        $data         = [];
        $data['logs'] = App::getDb()->fetchAll('SELECT * FROM tickets_logs WHERE ticket_id = ? ORDER BY id ASC', [$this->ticket->id]);

        return $data;
    }
}
