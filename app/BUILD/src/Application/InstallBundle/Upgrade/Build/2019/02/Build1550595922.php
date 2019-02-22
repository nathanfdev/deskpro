<?php

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;

class Build1550595922 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection();

        $spamId    = (int) $db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'spam'");
        $deletedId = (int) $db->fetchColumn("SELECT id FROM ticket_statuses where sys_id = 'deleted'");

        $ticketIds = $db->fetchColumn("SELECT data FROM tmp_data where name = 'ticket_ids' and auth = '1543332886'");
        $ticketIds = @json_decode($ticketIds, true);

        // For every ticket id in install_data, set the appropriate ticket_status_id for the Spam status.
        if ($ticketIds) {
            $ticketIds = array_map('intval', $ticketIds);
            foreach (array_chunk($ticketIds, 500) as $ids) {
                $db->executeUpdate('UPDATE tickets SET ticket_status_id = ? WHERE id IN (?)', [
                    $spamId,
                    $ids,
                ], [
                    \PDO::PARAM_INT,
                    Connection::PARAM_INT_ARRAY,
                ]);
            }
        }

        // For every ticket that still has status = "hidden" AND ticket_status_id IS NULL, set ticket_status_id for the Deleted status.
        // (i.e. after setting Spam, by process of elimination we know the rest are simply deleted).
        $db->executeUpdate("UPDATE tickets SET ticket_status_id = ? WHERE status='hidden' and ticket_status_id IS NULL", [
            $deletedId,
        ], [
            \PDO::PARAM_INT,
        ]);

        // For every ticket id in SELECT id FROM tickets_search_active WHERE is_hold = 1, set status = "pending"
        $db->executeUpdate("
            UPDATE tickets SET status='pending' WHERE id IN (
                SELECT id FROM tickets_search_active WHERE is_hold = 1
            )
        ");

        $db->delete('tmp_data', ['name' => 'ticket_ids', 'auth' => '1543332886']);
    }
}
