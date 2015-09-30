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

class Build1436977632 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $max_ticket_id = $db->fetchColumn('SELECT id FROM tickets ORDER BY id DESC LIMIT 1');
        $batch         = 0;
        $per_batch     = 25000;

        do {
            $this->out('Fix ticket org associations');

            $db->executeUpdate('
                UPDATE tickets
                JOIN people ON (people.id = tickets.person_id)
                SET tickets.organization_id = people.organization_id
                WHERE tickets.id BETWEEN ? AND ?
            ', array($batch + 1, $batch + $per_batch));

            $batch += $per_batch;
        } while ($batch < $max_ticket_id);

        $this->out('Reset tickets_search_active');
        $this->container->getEm()->getRepository('DeskPRO:Ticket')->fillSearchTable();
    }
}
