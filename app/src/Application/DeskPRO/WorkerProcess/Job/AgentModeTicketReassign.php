<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
        $db = App::getDb();
        $max = 2000;

        #------------------------------
        # Deleted -> unassign awaiting agent
        #------------------------------

        $agent_ids = $db->fetchAllCol("SELECT id FROM people WHERE is_agent = 1 AND is_deleted = 1");

        if ($max && $agent_ids) {

            $ticket_ids = $db->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE status IN ('awaiting_agent') AND agent_id IN (?)
                LIMIT $max
            ", array($agent_ids), array(Connection::PARAM_INT_ARRAY));

            if ($ticket_ids) {
                $chunks = array_chunk($ticket_ids, 250);
                foreach ($chunks as $ids) {
                    $log_batch = array();

                    $db->updateIn('tickets', array('agent_id' => null), $ids);
                    $db->updateIn('tickets_search_active', array('agent_id' => null), $ids);

                    foreach ($ids as $id) {
                        $log_batch[] = array(
                            'ticket_id'    => $id,
                            'action_type'  => 'free',
                            'details'      => serialize(array('message' => 'Unassigned deleted agent')),
                            'date_created' => date('Y-m-d H:i:s')
                        );
                    }

                    $db->batchInsert('tickets_logs', $log_batch, true);
                }

                $this->logStatus(sprintf("Unassigned %d awaiting_agent tickets from deleted agents", count($ticket_ids)));
            }
        }

        #------------------------------
        # Converted into a user -> unassign all
        #------------------------------

        $agent_ids = $db->fetchAllCol("SELECT id FROM people WHERE was_agent = 1 AND is_agent = 0");

        if ($max && $agent_ids) {

            $ticket_ids = $db->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE agent_id IN (?)
                LIMIT $max
            ", array($agent_ids), array(Connection::PARAM_INT_ARRAY));

            if ($ticket_ids) {
                $chunks = array_chunk($ticket_ids, 250);
                foreach ($chunks as $ids) {
                    $log_batch = array();

                    $db->updateIn('tickets', array('agent_id' => null), $ids);
                    $db->updateIn('tickets_search_active', array('agent_id' => null), $ids);

                    foreach ($ids as $id) {
                        $log_batch[] = array(
                            'ticket_id'    => $id,
                            'action_type'  => 'free',
                            'details'      => serialize(array('message' => 'Unassigned agent that was converted to a user')),
                            'date_created' => date('Y-m-d H:i:s')
                        );
                    }

                    $db->batchInsert('tickets_logs', $log_batch, true);
                }

                $this->logStatus(sprintf("Unassigned %d tickets from agents converted into users", count($ticket_ids)));
            }
        }
    }
}
