<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1480949357 extends AbstractBuild
{
    public function run()
    {
        $this->out('Adding index: ticket_slas.completed_id_status');
        $this->execSlowAlterTableQuiet('ticket_slas', 'ADD INDEX completed_id_status (is_completed, sla_id, sla_status)');

        $db = $this->getDbConnection();

        $incompleteSlas = $db->fetchColumn('SELECT COUNT(*) FROM ticket_slas WHERE is_completed = 0');
        $openTickets    = $db->fetchColumn("SELECT COUNT(*) FROM tickets_search_active WHERE status IN ('awaiting_agent', 'awaiting_user')");

        if ($incompleteSlas >= 10000 || $openTickets >= 10000) {
            $this->out('Enabling cached sla counts mode');
            $this->getDbConnection()->replace('settings', [
                'name'  => 'enable_cached_sla_counts',
                'value' => time(),
            ]);

            // insert 0's for all agnets to prevent coming back online
            // and killing the database with everyone doing it all at once
            $agentIds = $db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1 ORDER BY date_last_login DESC');
            $slaIds   = $db->fetchAllCol('SELECT id FROM slas');

            $expire = new \DateTime();

            foreach ($agentIds as $agentId) {
                $inserts = [];
                foreach ($slaIds as $slaId) {
                    $inserts[] = [
                        'person_id'   => $agentId,
                        'name'        => "ticket_sla_counts.{$slaId}",
                        'value_str'   => null,
                        'value_array' => serialize(['ok' => 0, 'warning' => 0, 'fail' => 0]),
                        'date_expire' => $expire->format('Y-m-d H:i:s'),
                    ];
                }
                if ($inserts) {
                    $db->batchInsert('people_prefs', $inserts);
                }
                $expire->modify('+10 minutes');
            }
        }
    }
}
