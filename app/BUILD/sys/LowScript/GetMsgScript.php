<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpSys\LowScript;

use Application\DeskPRO\App;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Util;

class GetMsgScript extends LowScriptAbstract
{
    protected $_person_id;
    protected $_session_id;

    public function runAction()
    {
        require_once DP_ROOT.'/src/Orb/Util/Util.php';
        require_once DP_ROOT.'/src/Orb/Util/Strings.php';

        try {
            $agent_session_id = isset($_COOKIE['dpsid-agent']) ? strval($_COOKIE['dpsid-agent']) : '';
            if (!$agent_session_id) {
                echo 'no session';
                exit;
            }

            if (!strpos($agent_session_id, '-')) {
                echo 'no session';
                exit;
            }
            list($session_id) = explode('-', $agent_session_id, 2);
            $session_id       = Util::baseDecode($session_id, Util::BASE36_ALPHABET);

            $agent_session = $this->getPdoRead()->query("
                SELECT sessions.*, people.is_agent, people_prefs.value_str AS last_message_id
                FROM sessions
                INNER JOIN people ON (sessions.person_id = people.id)
                LEFT JOIN people_prefs ON (people_prefs.person_id = people.id AND people_prefs.name = 'agent.ui.last_message_id')
                WHERE sessions.id = ".$this->getPdoRead()->quote($session_id)
            )->fetch(\PDO::FETCH_ASSOC);
            if (!$agent_session || $agent_session_id !== (Util::baseEncode($agent_session['id'], Util::BASE36_ALPHABET).'-'.$agent_session['auth'])) {
                echo 'no/invalid session';
                exit;
            }

            if (!$agent_session['is_agent']) {
                echo 'invalid session';
                exit;
            }

            $this->_person_id  = $agent_session['person_id'];
            $this->_session_id = $agent_session['id'];

            $new_since = isset($_REQUEST['since']) ? intval($_REQUEST['since']) : 0;
            if ($new_since < 0) {
                $new_since = 0;
            }
            $last_since    = intval($agent_session['last_message_id']);
            $activity_time = isset($_REQUEST['at']) ? intval($_REQUEST['at']) : 0;
            if ($activity_time < 0) {
                $activity_time = 0;
            }
            $is_initial_pool = !empty($_REQUEST['is_initial_poll']);

            //------------------------------
            // Dismissed client messages
            //------------------------------
            if (isset($_REQUEST['dismissed'])) {
                $notifications = [];

                $dismissed_notifications = $this->getDismissedNotifications();

                foreach ($dismissed_notifications as $notification) {
                    $notification['data'] = unserialize($notification['data']);

                    if (!empty($notification['data']['browser_rendered'])) {
                        $notifications[] = $notification['data']['browser_rendered'];
                    }
                }

                echo json_encode(['rendered_list' => implode("\n", $notifications)]);

                return true;
            }

            //------------------------------
            // Standard client messages
            //------------------------------

            $data = ['messages' => [], 'last_id' => -1];

            //------------------------------
            // Poll requests
            //------------------------------

            $dos = (isset($_REQUEST['do']) ? (array) $_REQUEST['do'] : []);

            // Every second poll, update online agents list
            $count = isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 0;
            if ($count < 0) {
                $count = 0;
            }
            if (($count && $count % 2 === 0) || $count == 1) {
                $dos[] = 'get-online-agents';
            }
            if ($count && $count % 3 === 0) {
                $dos[] = 'get-online-visitors';
            }

            if (isset($_REQUEST['get-custom-filters-data-batch'])) {
                $dos[] = 'get-custom-filters-data';
            }

            $dos = array_unique($dos);

            foreach ($dos as $do) {
                $do     = Strings::dashToCamelCase($do);
                $method = $do.'Message';

                if (!method_exists($this, $method)) {
                    continue;
                }

                $method_data = $this->$method();

                if ($method_data) {
                    $data['messages'] = array_merge($data['messages'], $method_data);
                }
            }

            // We save the last message we know a user got because we need to know
            // to deliver offline messages (such as chats) the next time the user logs in
            if ($new_since && $new_since > $last_since) {
                $q = $this->getPdo()->prepare('
                    REPLACE INTO people_prefs
                        (person_id, name, value_str, value_array, date_expire)
                    VALUES
                        (?, ?, ?, NULL, NULL);
                ');
                $q->execute([$agent_session['person_id'], 'agent.ui.last_message_id', $new_since]);
            }

            // See if we should update last activity time
            if ($activity_time && $activity_time > (time() - 330)) {
                // This bit makes sure theres only one record per 5 minute block
                $date_active         = new \DateTime('@'.$activity_time);
                list($hour, $minute) = explode(':', $date_active->format('H:i'));
                $minute              = intval($minute / 5) * 5;
                $date_active->setTime($hour, $minute, 0);

                $q = $this->getPdo()->prepare('
                    INSERT IGNORE INTO agent_activity
                        (agent_id, date_active)
                    VALUES
                        (?,?)
                ');
                $q->execute([$agent_session['person_id'], $date_active->format('Y-m-d H:i:s')]);
            }

            $secret = $this->_getSetting('core.app_secret');
            if (!$secret) {
                $secret = 'APP_SECRET';
            }

            $token                 = md5($agent_session['id'].$agent_session['auth'].$secret.'request_token');
            $data['request_token'] = Util::generateStaticSecurityToken($token, 10800);

            $q = $this->getPdo()->prepare('
                UPDATE sessions
                SET date_last = ?
                WHERE id = ?
            ');
            $q->execute([date('Y-m-d H:i:s', time()), $agent_session['id']]);

            if (!empty($_REQUEST['recent_tabs'])) {
                $post_recent_tabs = $_REQUEST['recent_tabs'];
                if (!is_array($post_recent_tabs)) {
                    $post_recent_tabs = @json_decode($post_recent_tabs, true);
                }

                if (!$post_recent_tabs) {
                    $post_recent_tabs = [];
                }

                $q = $this->getPdoRead()->prepare("
                    SELECT value_array
                    FROM people_prefs
                    WHERE person_id = ? AND name = 'agent.ui.recent_tabs_collection'
                ");
                $q->execute([$this->_person_id]);

                $recent_tabs = $q->fetchColumn();
                if ($recent_tabs) {
                    $recent_tabs = @unserialize($recent_tabs);
                }

                if (!$recent_tabs) {
                    $recent_tabs = [];
                }

                foreach ($post_recent_tabs as $item) {
                    if (empty($item[0]) || empty($item[1]) || empty($item[2]) || empty($item[3]) || empty($item[4]) || count($item) != 5) {
                        continue;
                    }

                    $id_string = $item[0].'-'.$item[1];
                    if (isset($recent_tabs[$id_string])) {
                        unset($recent_tabs[$id_string]);
                    }

                    $recent_tabs[$id_string] = $item;
                }

                uasort($recent_tabs, function ($a, $b) {
                    if ($a[4] == $b[4]) {
                        return 0;
                    }

                    return ($a[4] > $b[4]) ? -1 : 1;
                });

                while (count($recent_tabs) > 350) {
                    array_pop($recent_tabs);
                }

                $recent_tabs = serialize($recent_tabs);
                $this->getPdo()->prepare("
                    REPLACE INTO people_prefs
                    SET
                        person_id = ?,
                        name = 'agent.ui.recent_tabs_collection',
                        value_str = NULL,
                        value_array = ?,
                        date_expire = NULL
                ")->execute([
                    $this->_person_id,
                    $recent_tabs,
                ]);
            }

            //------------------------------
            // Dismiss messages
            //------------------------------

            if (!empty($_REQUEST['dismiss_alerts']) && is_array($_REQUEST['dismiss_alerts'])) {
                $ids = $_REQUEST['dismiss_alerts'];
                $ids = Arrays::castToType($ids, 'int', 'discard');
                $ids = Arrays::removeFalsey($ids);
                $ids = array_unique($ids);

                if ($ids) {
                    if (in_array('-1', $ids)) {
                        $this->getPdo()->exec("
                            UPDATE agent_alerts
                            SET is_dismissed = 1
                            WHERE person_id = {$agent_session['person_id']}
                        ");
                    } else {
                        $ids_in = implode(',', $ids);
                        $this->getPdo()->exec("
                            UPDATE agent_alerts
                            SET is_dismissed = 1
                            WHERE person_id = {$agent_session['person_id']} AND id IN ($ids_in)
                        ");
                    }
                }
            }

            // First poll, re-load up to the last 100 alerts
            if ($is_initial_pool) {
                $q = $this->getPdoRead()->query("
                    SELECT id, typename, data
                    FROM agent_alerts
                    WHERE person_id = {$agent_session['person_id']} AND is_dismissed = 0 AND typename IN ('tickets')
                    ORDER BY id ASC
                    LIMIT 100
                ");
                $q->execute();

                $count         = 0;
                $last_alert_id = null;
                while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
                    if ($last_alert_id === null || $r['id'] < $last_alert_id) {
                        $last_alert_id = $r['id'];
                    }
                    ++$count;

                    $r['data']          = unserialize($r['data']);
                    $data['messages'][] = [
                        null,
                        'agent-notify.'.$r['typename'],
                        [
                            'type'     => $r['typename'],
                            'alert_id' => (int) $r['id'],
                            'row'      => $r['data']['browser_rendered'],
                        ],
                    ];
                }

                if ($count == 100 && $last_alert_id) {
                    $this->getPdo()->exec("
                        UPDATE agent_alerts
                        SET is_dismissed = 1
                        WHERE person_id = {$agent_session['person_id']} AND id < $last_alert_id
                    ");
                }
            }

            $data['action_alerts'] = $this->getActionAlerts();
            $readNotifications     = false;
            foreach ($data['action_alerts'] as $k => $actionAlert) {
                if ($actionAlert['type'] === 'read.notifications.alert') {
                    $readNotifications = true;
                    // we're processing it further to avoid sending read notifications.alert data
                    unset($data['action_alerts'][$k]);
                }
            }
            $data['action_alerts'] = array_values($data['action_alerts']);
            $data['notifications'] = $readNotifications ? $this->getNotifications() : [];

            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (\Exception $exception) {
            if ($this->dpEnv->isDebug()) {
                echo "\n\n[{$exception->getCode()}] {$exception->getMessage()}\n\n";

                $backtrace = $exception->getTrace();
                $trace     = self::formatBacktrace($backtrace);
                echo $trace;
            }

            $this->handleException($exception);
        }
    }

    //###########################################################################
    // getFilterCounts
    //###########################################################################

    public function getSysFiltersDataMessage()
    {
        $this->_getContainer();
        $filters_api = new \Application\DeskPRO\Tickets\Filters();

        $filter_info = $filters_api->getGroupedFiltersForPerson($this->_getPerson());
        $filters     = [];
        foreach (['sys_filters', 'sys_filters_hold', 'archive_filters'] as $k) {
            foreach ($filter_info[$k] as $f) {
                $filters[$f->id] = $f;
            }
        }

        $client_counts = null;
        if (isset($_REQUEST['filters_data_counts'])) {
            $client_counts = $_REQUEST['filters_data_counts'];
            if ($client_counts) {
                $client_counts = @json_decode($client_counts, true);
            }
        }
        if (!$client_counts) {
            $client_counts = [];
        }
        $filter_id_matches = App::getApi('tickets.filters')->getAllIdsForFiltersCollection($filters, $this->_getPerson());
        $filter_id_matches = Arrays::castToTypeDeep($filter_id_matches, 'int', 'int');

        $filter_counts = [];
        $prefs         = App::getDb()->fetchAllKeyValue("
            SELECT name, value_str
            FROM people_prefs
            WHERE name LIKE 'ticket_counts.' AND person_id = ?
        ", [$this->_getPerson()->getId()]);
        foreach ($filters as $f) {
            if (isset($filter_id_matches[$f->id])) {
                $filter_counts[$f->id] = count($filter_id_matches[$f->id]);
            } elseif ($f->isArchiveTableFilter()) {
                $pref_key = 'ticket_counts.'.$f->sys_name;
                if (isset($prefs[$pref_key])) {
                    $filter_counts[$f->id] = (int) $prefs[$pref_key];
                } else {
                    $filter_counts[$f->id] = intval(App::getSetting('core_tablecounts.tickets.'.$f->sys_name) ?: 0);
                }
            }
        }

        // When the counts are the same, we dont send the full list
        // back to the client. This reduces the request size on lists that dont change
        if ($client_counts) {
            foreach (array_keys($filter_id_matches) as $filter_id) {
                if (isset($client_counts[$filter_id]) && $client_counts[$filter_id] == count($filter_id_matches[$filter_id])) {
                    unset($filter_id_matches[$filter_id]);
                }
            }
        }

        $filter_counts = Arrays::castToTypeDeep($filter_counts, 'int', 'int');

        $filter_data = [
            'ids'    => $filter_id_matches,
            'counts' => $filter_counts,
        ];

        return [
            [null, 'filters.filter_data', $filter_data],
        ];
    }

    public function getCustomFiltersDataMessage()
    {
        $this->_getContainer();
        $filters_api = new \Application\DeskPRO\Tickets\Filters();

        $filter_info    = $filters_api->getGroupedFiltersForPerson($this->_getPerson());
        $custom_filters = $filter_info['custom_filters'];

        if (!empty($_REQUEST['get-custom-filters-data-ignore'])) {
            $ignore         = explode(',', $_REQUEST['get-custom-filters-data-ignore']);
            $custom_filters = array_filter($custom_filters, function ($f) use ($ignore) {
                return !in_array($f['id'], $ignore);
            });
        }

        if (!$custom_filters) {
            return [];
        }

        $batched  = array_chunk($custom_filters, 10);
        $batchNum = !empty($_REQUEST['get-custom-filters-data-batch']) ? $_REQUEST['get-custom-filters-data-batch'] : 0;
        if (!isset($batched[$batchNum])) {
            return [];
        }

        $filter_counts = App::getApi('tickets.filters')->getAllCountsForFiltersCollection($batched[$batchNum], $this->_getPerson());

        if (isset($batched[$batchNum + 1])) {
            $nextBatch = $batchNum + 1;
        } else {
            $nextBatch = null;
        }

        $filter_data = [
            'ids'        => [],
            'counts'     => $filter_counts,
            'next_batch' => $nextBatch,
        ];

        return [[null, 'filters.filter_data', $filter_data]];
    }

    //###########################################################################
    // getFlaggedCounts
    //###########################################################################

    public function getFlaggedCountsMessage()
    {
        $this->_getContainer();
        $filters    = new \Application\DeskPRO\Tickets\Filters();
        $all_counts = $filters->getAllCountsForPersonFlagged($this->_getPerson());

        return [[null, 'filter-flagged.counts', [$all_counts]]];
    }

    //###########################################################################
    // getCheckTickets
    //###########################################################################

    public function checkTicketsMessage()
    {
        $ticket_ids = isset($_REQUEST['check-ticket-ids']) ? (array) $_REQUEST['check-ticket-ids'] : [];
        $ticket_ids = array_map('intval', $ticket_ids);

        $tickets = $this->_getContainer()->getEm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

        $messages = [];
        foreach ($tickets as $ticket) {
            $msg_id   = 'tickets.check.'.$ticket['id'];
            $msg_data = [];

            $msg_data['is_locked'] = $ticket->isLocked();

            $messages[$msg_id] = $msg_data;
        }

        return $messages;
    }

    //###########################################################################
    // getOnlineVisitors
    //###########################################################################

    public function getOnlineVisitorsMessage()
    {
        return [
            [null, 'agent.online-users-count', ['online_count' => 0]],
        ];
    }

    //###########################################################################
    // getOnlineAgents
    //###########################################################################

    public function getOnlineAgentsMessage()
    {
        $timeout = $this->_getSetting('core_chat.agent_timeout', 120);
        $cutoff  = date('Y-m-d H:i:s', time() - $timeout);

        $q = $this->getPdoRead()->prepare("
            SELECT DISTINCT s.person_id
            FROM sessions s
            INNER JOIN people p ON (s.person_id = p.id)
            WHERE (p.is_agent = 1 AND p.is_deleted = 0 AND s.date_last > ? AND interface = 'agent')
                OR s.person_id = ?
        ");
        $q->execute([$cutoff, $this->_person_id]);

        $online_agents = [];
        while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
            $online_agents[] = $row['person_id'];
        }

        $q = $this->getPdoRead()->prepare("
            SELECT DISTINCT person_id
            FROM sessions
            WHERE date_last >= ? AND active_status = 'available' AND is_person = 1 AND is_chat_available = 1 AND interface = 'agent'
        ");
        $q->execute([$cutoff]);

        $online_agents_userchat = [];
        while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
            $online_agents_userchat[] = $row['person_id'];
        }

        return [
            [null, 'agent.online-agents', ['online_agents' => $online_agents]],
            [null, 'agent.online-agents-userchat', ['online_agents' => $online_agents_userchat]],
        ];
    }

    protected $_settings;

    protected function _getSetting($name, $default = null)
    {
        if (!$this->_settings) {
            $this->_settings = [];
            $q               = $this->getPdoRead()->prepare('
                SELECT name, value
                FROM settings
            ');
            $q->execute();
            while ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
                $this->_settings[$row['name']] = $row['value'];
            }
        }

        return isset($this->_settings[$name]) ? $this->_settings[$name] : $default;
    }

    protected $_container;

    protected function _getContainer()
    {
        if (!$this->_container) {
            $this->_container = $this->bootFullSystem('DeskPRO\\Kernel\\AgentKernel');
        }

        return $this->_container;
    }

    protected $_person;

    protected function _getPerson()
    {
        if (!$this->_person) {
            $this->_person = $this->_getContainer()->getEm()->getRepository('DeskPRO:Person')->find($this->_person_id);

            $this->_person->loadHelper('Agent');
            $this->_person->loadHelper('AgentTeam');
            $this->_person->loadHelper('AgentPermissions');
            $this->_person->loadHelper('PermissionsManager');
            $this->_person->loadHelper('HelpMessages');
            $this->_person->loadHelper('AgentPrefs');

            App::setCurrentPerson($this->_person);
        }

        return $this->_person;
    }

    protected function getDismissedNotifications()
    {
        if (!$this->_person_id) {
            return [];
        }

        $q = $this->getPdoRead()->query("
            SELECT id, typename, data
            FROM agent_alerts
            WHERE person_id = {$this->_person_id} AND is_dismissed = 1
            ORDER BY id DESC
            LIMIT 100
        ");
        $q->execute();

        return $q->fetchAll();
    }

    protected function getActionAlerts()
    {
        if (!$this->_person_id || !isset($_REQUEST['last_alert'])) {
            return [];
        }
        $last = (int) $_REQUEST['last_alert'];

        return $this->transformData($this->fetch($last, $this->_person_id));
    }

    protected function getNotifications()
    {
        if (!$this->_person_id || !isset($_REQUEST['last_notify'])) {
            return [];
        }
        $last = (int) $_REQUEST['last_notify'];

        return $this->transformData($this->fetch($last, $this->_person_id, 'notifications'));
    }

    protected function fetch($last, $targetId, $type = 'action_alerts')
    {
        $tableName = 'notify_'.$type;
        $sql       = <<<SQL
SELECT * FROM `{$tableName}`
WHERE `target_id` = :target_id 
  AND `id` > :last
ORDER BY `id` ASC
SQL;
        $stmnt = $this->getPdoRead()->prepare($sql);
        $stmnt->execute([
            'target_id' => $targetId,
            'last'      => $last,
        ]);

        $all = $stmnt->fetchAll(\PDO::FETCH_ASSOC);

        return $all;
    }

    protected function transformData($data)
    {
        foreach ($data as &$datum) {
            foreach ($datum as &$innerData) {
                if (is_numeric($innerData)) {
                    $innerData = (int) $innerData;
                }
            }
            $date                  = new \DateTime($datum['date_created']);
            $datum['date_created'] = $date->format(\DateTime::ISO8601);
            $datum['timestamp']    = $date->getTimestamp();
        }

        return $data;
    }
}
