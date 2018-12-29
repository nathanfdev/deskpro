<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Util as TicketUtil;

/**
 * Goes through tickets marked as spam and deletes them.
 */
class DeleteSpamTickets extends AbstractJob
{
    const DEFAULT_INTERVAL = 86400;

    public function run()
    {
        $secs = App::getSetting('core_tickets.spam_delete_time');

        // 0 means disable
        if ($secs < 1) {
            return;
        }

        $date_cut = new \DateTime('@'.(time() - $secs));

        //------------------------------
        // find tickets to proc
        //------------------------------

        $ticket_count = 0;

        $spamStatusId = (int) App::getContainer()->getTicketStatuses()->getSpamStatus()->getId();
        $all_tickets  = App::getDb()->fetchAll('
			SELECT id, person_id
			FROM tickets
			WHERE tickets.ticket_status_id = ? AND tickets.date_status < ?
			LIMIT 1000
		', [$spamStatusId, $date_cut->format('Y-m-d H:i:s')]);

        $this->logger->log(sprintf('[DeleteSpamTickets] %d tickets to delete', count($all_tickets)), 'DEBUG');
        $date_str = date('Y-m-d H:i:s');

        foreach ($all_tickets as $ticket) {
            App::getDb()->beginTransaction();
            try {
                $this->logger->log(sprintf('[DeleteSpamTickets] Deleted ticket %d', $ticket['id']), 'DEBUG');

                TicketUtil::deleteTicketAttachments($ticket['id'], App::getDb());

                App::getDb()->delete('tickets_deleted', ['ticket_id' => $ticket['id']]);
                App::getDb()->replace('tickets_deleted', ['ticket_id' => $ticket['id'], 'by_person_id' => null, 'new_ticket_id' => 0, 'date_created' => $date_str, 'reason' => 'Deleted as spam (system cleanup)']);

                App::getDb()->delete('tickets', ['id' => $ticket['id']]);
                App::getDb()->delete('tickets_search_active', ['id' => $ticket['id']]);

                ++$ticket_count;

                App::getDb()->commit();
            } catch (\Exception $e) {
                App::getDb()->rollback();
                throw $e;
            }
        }

        if ($ticket_count) {
            $this->logStatus('Removed '.count($ticket_count).' spam tickets');
        }
    }
}
