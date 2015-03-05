<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Doctrine\DBAL\Connection;

/**
 * Archives old tickets
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
        $now = date('Y-m-d H:i:s');

        $ticket_ids = App::getDb()->fetchAllCol("
            SELECT id FROM tickets_search_active
            WHERE status = 'resolved' AND date_resolved < ?
            LIMIT 3000
        ", array($datecut));

        $count = count($ticket_ids);
        $ticket_ids = array_chunk($ticket_ids, 500, false);

        $details_arr = serialize(array(
            'old_status' => 'resolved',
            'new_status' => 'archived'
        ));

        foreach ($ticket_ids as $ids) {

            // Re-fetch IDs from tickets table in case
            // search table is corrupt
            $ids = App::getDb()->fetchAllCol("
                SELECT id FROM tickets
                WHERE id IN (?) AND status = 'resolved'
            ", array($ids), array(Connection::PARAM_INT_ARRAY));

            if (!$ids) {
                continue;
            }

            $batch = array();
            foreach ($ids as $id) {
                $batch[] = array(
                    'ticket_id'    => $id,
                    'action_type'  => 'changed_status',
                    'id_before'    => 200,
                    'id_after'     => 210,
                    'details'      => $details_arr,
                    'date_created' => $now
                );
            }

            App::getDb()->executeUpdate("
                UPDATE tickets
                SET status = 'archived', date_archived = ?, date_status = ?
                WHERE id IN (?)
            ", array($now, $now, $ids), array(\PDO::PARAM_STR, \PDO::PARAM_STR, Connection::PARAM_INT_ARRAY));

            App::getDb()->batchInsert('tickets_logs', $batch);

            App::getDb()->executeUpdate("
                DELETE FROM tickets_search_active
                WHERE id IN (?)
            ", array($ids), array(Connection::PARAM_INT_ARRAY));
        }

        if ($count) {
            $this->logStatus("$count tickets archived");
        }
    }
}
