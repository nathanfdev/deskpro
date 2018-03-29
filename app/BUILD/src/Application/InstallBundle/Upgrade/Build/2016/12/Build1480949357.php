<?php

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
