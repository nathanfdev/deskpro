<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSearchActive;

/**
 * Updates the archive tables.
 */
class TicketSearchUpdater
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    public function __construct(Connection $db, Ticket $ticket)
    {
        $this->db     = $db;
        $this->ticket = $ticket;
    }

    /**
     * Remove the ticket from search tables.
     */
    public function remove()
    {
        $this->db->delete('tickets_search_active', ['id' => $this->ticket->getOriginalId()]);
    }

    /**
     * Update or add ticket to search tables.
     */
    public function update()
    {
        if (!$this->ticket->isArchived()) {
            $cols = TicketSearchActive::getFieldNamesAsSqlString();
            $data = $this->db->fetchAssoc("SELECT {$cols} FROM tickets WHERE id = ?", [$this->ticket->id]);
            if ($data && $data['status'] != 'archived') {
                $this->db->replace('tickets_search_active', $data);
            } else {
                $this->db->delete('tickets_search_active', ['id' => $this->ticket->id]);
            }
        } else {
            $this->db->delete('tickets_search_active', ['id' => $this->ticket->id]);
        }
    }
}
