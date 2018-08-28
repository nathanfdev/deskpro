<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Logs\OptionsModel;

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
        $this->cleanupLogs();

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

    private function cleanupLogs()
    {
        // cleanup by time
        $settingsBag = App::getContainer()->get('settings_resolver')->getGlobalSettings();
        $keepDays    = $settingsBag->get('api_log.max_logs_keep_days', OptionsModel::DEFAULT_MAX_KEEP_DAYS);
        $dateTime    = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateTime->modify(sprintf('-%d days', $keepDays));

        App::getContainer()->getDb()->executeUpdate('
            DELETE FROM `api_key_log`
            WHERE `api_key_log`.`time` < ?',
            [$dateTime->getTimestamp()]
        );

        App::getContainer()->getDb()->executeUpdate('
            DELETE FROM `api_log`
            WHERE `api_log`.`end_time` < ?',
            [$dateTime->getTimestamp()]
        );

        // cleanup by max logs count (old logs)

        $perKey = $settingsBag->get('api_log.max_logs_per_key', OptionsModel::DEFAULT_MAX_PER_KEY);

        $keyIds = App::$container->getDb()->fetchAllCol('
            SELECT `api_key_log`.`key_id`
            FROM `api_key_log`
            GROUP BY `api_key_log`.`key_id`
            HAVING COUNT(*) > ?
        ', [$perKey], [\PDO::PARAM_INT]);

        foreach ($keyIds as $keyId) {
            $lid = App::$container->getDb()->fetchColumn("
                SELECT `id`
                FROM `api_key_log`
                WHERE `api_key_log`.`key_id` = ?
                ORDER BY `api_key_log`.`id` DESC
                LIMIT {$perKey}, 1
            ", [$keyId]);

            if ($lid) {
                App::$container->getDb()->executeUpdate('
                    DELETE FROM `api_key_log`
                    WHERE `api_key_log`.`key_id` = ? AND `api_key_log`.`id` <= ?
                ', [$keyId, $lid]);
            }
        }

        unset($keyIds);
        unset($keyId);
        unset($lid);

        // cleanup by max logs count (old logs)

        $keyIds = App::$container->getDb()->fetchAllCol('
            SELECT `api_log`.`api_key_id`
            FROM `api_log`
            GROUP BY `api_log`.`api_key_id`
            HAVING COUNT(*) > ?
        ', [$perKey], [\PDO::PARAM_INT]);

        foreach ($keyIds as $keyId) {
            $lid = App::$container->getDb()->fetchColumn("
                SELECT `api_log`.`id`
                FROM `api_log`
                WHERE `api_log`.`api_key_id` = ?
                ORDER BY `api_log`.`id` DESC
                LIMIT {$perKey}, 1
            ", [$keyId]);

            if ($lid) {
                App::$container->getDb()->executeUpdate('
                    DELETE FROM `api_log`
                    WHERE `api_log`.`api_key_id` = ? AND `api_log`.`id` <= ?
                ', [$keyId, $lid]);
            }
        }
    }
}
