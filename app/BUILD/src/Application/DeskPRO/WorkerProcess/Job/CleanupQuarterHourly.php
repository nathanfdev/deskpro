<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class CleanupQuarterHourly extends AbstractJob
{
    const DEFAULT_INTERVAL = 900;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        //------------------------------
        // Old API logs
        //------------------------------

        App::$container->getEm()->getRepository('DeskPRO:ApiKeyLog')->cleanup();

        //------------------------------
        // Update table counts
        //------------------------------

        $counts                               = [];
        $counts['tickets']                    = App::getDbRead('search.filter.tickets')->fetchColumn('SELECT COUNT(*) FROM `tickets`');
        $counts['tickets.resolved']           = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets_search_active` WHERE `status` = 'resolved'");
        $counts['tickets.awaiting_user']      = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets_search_active` WHERE `status` = 'awaiting_user'");
        $counts['tickets.archive_validating'] = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'validating'");
        $counts['tickets.archive_spam']       = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'spam'");
        $counts['tickets.archive_deleted']    = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'hidden' AND `hidden_status` = 'deleted'");
        $counts['tickets.archive_archived']   = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets` WHERE `status` = 'archived'");
        $counts['people']                     = App::getDbRead('search.filter.tickets')->fetchColumn('SELECT COUNT(*) FROM `people`');

        foreach ($counts as $k => $v) {
            App::getDb()->replace('settings', [
                'name'  => "core_tablecounts.$k",
                'value' => (int) $v,
            ]);
        }

        $did_per_agent_filters = false;

        // - We only do per-agent numbers on archive filters if we
        // have fewer than 1m tickets total. More than that, exact
        // numbers are unlikely to matter anyway, but it may get slow.
        // - We also ignore per-agent numbers if we have lots of agents,
        // just so we dont run so many useless queries at once.

        // If this is skipped, the UI will simply use the normal global counts above

        if ($counts['tickets'] < 1000000) {
            $all_agents = App::getContainer()->getAgentData()->getAgents();
            if (count($all_agents) < 75) {
                $did_per_agent_filters = true;

                // Fetch in agent context
                $filters = App::getOrm()->createQuery("
                    SELECT f
                    FROM DeskPRO:LegacyTicketFilter f
                    WHERE f.sys_name LIKE 'archive_%'
                ")->execute();

                $inserts = [];

                foreach ($all_agents as $agent) {
                    $agent->loadHelper('Agent');
                    $agent->loadHelper('AgentTeam');
                    $agent->loadHelper('AgentPermissions');
                    $agent->loadHelper('PermissionsManager');
                    $agent->loadHelper('HelpMessages');
                    $agent->loadHelper('AgentPrefs');

                    foreach ($filters as $filter) {
                        /* @var \Application\DeskPRO\Entity\LegacyTicketFilter $filter*/
                        $searcher = $filter->getSearcher();
                        $searcher->setPersonContext($agent);
                        $searcher->setOrderBy('ticket.date_created');

                        $count = $searcher->getCount();

                        $inserts[] = [
                            'person_id'   => $agent->id,
                            'name'        => "ticket_counts.{$filter->sys_name}",
                            'value_str'   => $count,
                            'value_array' => null,
                            'date_expire' => null,
                        ];
                    }
                }

                if ($inserts) {
                    App::getDb()->executeUpdate("DELETE FROM people_prefs WHERE name LIKE 'ticket_counts.%'");
                    App::getDb()->batchInsert('people_prefs', $inserts, true);
                }
            }
        }

        if (!$did_per_agent_filters) {
            App::getDb()->executeUpdate("DELETE FROM people_prefs WHERE name LIKE 'ticket_counts.%'");
        }

        //------------------------------
        // Enable cached slas
        //------------------------------

        if (!$this->getContainer()->getSetting('enable_cached_sla_counts')) {
            $incompleteSlas     = App::getDbRead('search.filter.tickets')->fetchColumn('SELECT COUNT(*) FROM ticket_slas WHERE is_completed = 0');
            $awaitingAgentcount = App::getDbRead('search.filter.tickets')->fetchColumn("SELECT COUNT(*) FROM `tickets_search_active` WHERE `status` = 'awaiting_agent'");
            if ($incompleteSlas >= 10000 || ($counts['tickets.awaiting_user'] + $awaitingAgentcount) >= 10000) {
                $this->getContainer()->getDb()->replace('settings', [
                    'name'  => 'enable_cached_sla_counts',
                    'value' => time(),
                ]);
            }
        }
    }
}
