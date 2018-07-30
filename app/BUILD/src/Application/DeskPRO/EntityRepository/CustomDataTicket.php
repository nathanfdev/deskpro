<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

class CustomDataTicket extends AbstractEntityRepository
{
    public function getDataForTicket(Entity\Ticket $ticket)
    {
        return $this->_em->createQuery('
            SELECT d
            FROM DeskPRO:CustomDataTicket d INDEX BY d.field_id
            WHERE d.ticket = ?1
        ')->setParameter(1, $ticket)->execute();
    }

    /**
     * Fetch data for a whole bunch of things.
     *
     * @param array $tickets
     *
     * @return array
     */
    public function getDataCollectionForTicketCollection(array $tickets)
    {
        $ids = [];
        foreach ($tickets as $t) {
            $ids[] = $t->id;
        }

        if (!$ids) {
            return [];
        }

        $raw = $this->_em->createQuery('
            SELECT d
            FROM DeskPRO:CustomDataTicket d
            LEFT JOIN d.ticket t
            WHERE d.ticket.id IN (?0)
        ')->execute([$ids]);

        if (!$raw) {
            return [];
        }

        // Note that this is still pretty inefficient. Doctrine doesnt know about the
        // 'ticket_id' field in the table, only the ticket object. So we had to join
        // on the ticket table to fetch it, just so we can use the id to index the array below

        // Reindex by ticket id
        $data = [];
        foreach ($raw as $r) {
            if (!isset($data[$r->ticket->id])) {
                $data[$r->ticket->id] = [];
            }

            $data[$r->ticket->id][] = $r;
        }

        return $data;
    }
}
