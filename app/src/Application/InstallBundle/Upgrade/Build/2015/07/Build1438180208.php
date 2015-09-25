<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace Application\InstallBundle\Upgrade\Build;

class Build1438180208 extends AbstractBuild
{
    public function run()
    {
        $this->out('Resetting date_resolved on archived tickets without it');
        $db = $this->container->getDb();

        $batch_size = 1000;

        while (true) {
            $ticket_ids = $db->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE status = 'archived' AND date_resolved IS NULL
                LIMIT $batch_size
            ");

            if (!$ticket_ids) {
                break;
            }

            foreach ($ticket_ids as $tid) {
                // id_after = 200 is from Ticket::getStatusInt
                $date_resolved = $db->fetchColumn("
                    SELECT date_created
                    FROM tickets_logs
                    WHERE ticket_id = ? AND action_type = 'changed_status' AND id_after = 200
                    ORDER BY id DESC
                    LIMIT 1
                ", array($tid));

                if (!$date_resolved) {
                    $db->executeUpdate('
                        UPDATE tickets SET date_resolved = COALESCE(date_status, date_created, NOW())
                        WHERE id = ?
                    ', array($tid));
                } else {
                    $db->executeUpdate('
                        UPDATE tickets SET date_resolved = ?
                        WHERE id = ?
                    ', array($date_resolved, $tid));
                }
            }

            $this->out('Done batch of '.count($ticket_ids).'...');
        }
    }
}
