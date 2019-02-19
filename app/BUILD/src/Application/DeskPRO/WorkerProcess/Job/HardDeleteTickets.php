<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Util as TicketUtil;

/**
 * Goes through soft-deleted tickets that were deleted long ago,
 * and permanently removes them now.
 */
class HardDeleteTickets extends AbstractJob
{
    const DEFAULT_INTERVAL = 86400;

    public function run()
    {
        $secs = App::getSetting('core_tickets.hard_delete_time');

        // 0 means disable
        if ($secs < 1) {
            return;
        }

        $date_cut = date('Y-m-d H:i:s', time() - $secs);

        //------------------------------
        // find tickets to proc
        //------------------------------

        $deletedStatusId = (int) App::getContainer()->getTicketStatuses()->getDeletedStatus()->getId();
        $ticket_ids      = App::getDb()->fetchAllCol('
            SELECT tickets_deleted.ticket_id
            FROM tickets_deleted
            LEFT JOIN tickets ON (tickets.id = tickets_deleted.ticket_id)
            WHERE tickets_deleted.date_created < ?
            AND tickets.id IS NOT NULL
            AND tickets.ticket_status_id = ?
            LIMIT 5000
        ', [$date_cut, $deletedStatusId]);

        foreach ($ticket_ids as $ticket_id) {
            App::getDb()->beginTransaction();

            try {
                TicketUtil::deleteTicketAttachments($ticket_id, App::getDb());

                // Ticket log already has the deletion record, we're doing the physical delete of the actual rows here
                App::getDb()->delete('tickets_search_active', ['id' => $ticket_id]);
                App::getDb()->delete('tickets', ['id' => $ticket_id]);
                App::getDb()->commit();
            } catch (\Exception $e) {
                App::getDb()->rollback();
                throw $e; // rethrow for error logging etc
            }
        }

        if ($ticket_ids) {
            $this->logStatus('Removed '.count($ticket_ids).' old soft-deleted tickets');
        }
    }
}
