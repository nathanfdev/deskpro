<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Doctrine\DBAL\Connection;

/**
 * Archives old tickets.
 */
class ArchiveTickets extends AbstractJob
{
    const DEFAULT_INTERVAL = 14400; // 4 hours

    public function run()
    {
        if (!App::getSetting('core_tickets.use_archive')) {
            return;
        }

        $datecut = date('Y-m-d H:i:s', time() - App::getSetting('core_tickets.auto_archive_time'));
        $now     = date('Y-m-d H:i:s');

        $ticket_ids = App::getDb()->fetchAllCol("
            SELECT id FROM tickets_search_active
            WHERE status = 'resolved' AND date_resolved < ?
            LIMIT 3000
        ", [$datecut]);

        $count      = count($ticket_ids);
        $ticket_ids = array_chunk($ticket_ids, 500, false);

        $details_arr = serialize([
            'old_status' => 'resolved',
            'new_status' => 'archived',
        ]);

        foreach ($ticket_ids as $ids) {
            // Re-fetch IDs from tickets table in case
            // search table is corrupt
            $ids = App::getDb()->fetchAllCol("
                SELECT id FROM tickets
                WHERE id IN (?) AND status = 'resolved'
            ", [$ids], [Connection::PARAM_INT_ARRAY]);

            if (!$ids) {
                continue;
            }

            $batch = [];
            foreach ($ids as $id) {
                $batch[] = [
                    'ticket_id'    => $id,
                    'action_type'  => 'changed_status',
                    'id_before'    => 200,
                    'id_after'     => 210,
                    'details'      => $details_arr,
                    'date_created' => $now,
                ];
            }

            App::getDb()->executeUpdate("
                UPDATE tickets
                SET status = 'archived', date_archived = ?, date_status = ?
                WHERE id IN (?)
            ", [$now, $now, $ids], [\PDO::PARAM_STR, \PDO::PARAM_STR, Connection::PARAM_INT_ARRAY]);

            App::getDb()->batchInsert('tickets_logs', $batch);

            App::getDb()->executeUpdate('
                DELETE FROM tickets_search_active
                WHERE id IN (?)
            ', [$ids], [Connection::PARAM_INT_ARRAY]);
        }

        if ($count) {
            $this->logStatus("$count tickets archived");
        }
    }
}
