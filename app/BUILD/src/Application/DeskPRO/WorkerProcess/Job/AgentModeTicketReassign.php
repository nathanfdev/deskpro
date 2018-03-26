<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Doctrine\DBAL\Connection;

/**
 * When an agent enters vacation mode or is deleted, we have to re-assign their awaiting_agent tickets
 * to unassigned. This does them in batches.
 *
 * We cant just do it in mysql because we need the proper logs to be generated.
 */
class AgentModeTicketReassign extends AbstractJob
{
    const DEFAULT_INTERVAL = 300;

    public function run()
    {
        $db  = App::getDb();
        $max = 2000;

        //------------------------------
        // Deleted -> unassign awaiting agent
        //------------------------------

        $agent_ids = $db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1 AND is_deleted = 1');

        if ($max && $agent_ids) {
            $ticket_ids = $db->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE status IN ('awaiting_agent') AND agent_id IN (?)
                LIMIT $max
            ", [$agent_ids], [Connection::PARAM_INT_ARRAY]);

            if ($ticket_ids) {
                $chunks = array_chunk($ticket_ids, 250);
                foreach ($chunks as $ids) {
                    $log_batch = [];

                    $db->updateIn('tickets', ['agent_id' => null], $ids);
                    $db->updateIn('tickets_search_active', ['agent_id' => null], $ids);

                    foreach ($ids as $id) {
                        $log_batch[] = [
                            'ticket_id'    => $id,
                            'action_type'  => 'free',
                            'details'      => serialize(['message' => 'Unassigned deleted agent']),
                            'date_created' => date('Y-m-d H:i:s'),
                        ];
                    }

                    $db->batchInsert('tickets_logs', $log_batch, true);
                }

                $this->logStatus(sprintf('Unassigned %d awaiting_agent tickets from deleted agents', count($ticket_ids)));
            }
        }

        //------------------------------
        // Converted into a user -> unassign all
        //------------------------------

        $agent_ids = $db->fetchAllCol('SELECT id FROM people WHERE was_agent = 1 AND is_agent = 0');

        if ($max && $agent_ids) {
            $ticket_ids = $db->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE agent_id IN (?)
                LIMIT $max
            ", [$agent_ids], [Connection::PARAM_INT_ARRAY]);

            if ($ticket_ids) {
                $chunks = array_chunk($ticket_ids, 250);
                foreach ($chunks as $ids) {
                    $log_batch = [];

                    $db->updateIn('tickets', ['agent_id' => null], $ids);
                    $db->updateIn('tickets_search_active', ['agent_id' => null], $ids);

                    foreach ($ids as $id) {
                        $log_batch[] = [
                            'ticket_id'    => $id,
                            'action_type'  => 'free',
                            'details'      => serialize(['message' => 'Unassigned agent that was converted to a user']),
                            'date_created' => date('Y-m-d H:i:s'),
                        ];
                    }

                    $db->batchInsert('tickets_logs', $log_batch, true);
                }

                $this->logStatus(sprintf('Unassigned %d tickets from agents converted into users', count($ticket_ids)));
            }
        }
    }
}
