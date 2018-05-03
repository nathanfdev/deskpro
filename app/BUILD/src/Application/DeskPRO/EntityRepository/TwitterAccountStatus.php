<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TwitterAccount as TwitterAccountEntity;

class TwitterAccountStatus extends AbstractEntityRepository
{
    const DEFAULT_LIMIT = 50;

    public function getByTwitterStatusAndAccount($id, $account)
    {
        return $this->getEntityManager()->createQuery('
            SELECT s, t, u
            FROM DeskPRO:TwitterAccountStatus s
            INNER JOIN s.status t
            INNER JOIN t.user u
            WHERE s.status = ?0
                AND s.account = ?1
        ')->setParameters([$id, $account])->getOneOrNullResult();
    }

    public function getByTwitterIdsAndAccount(array $ids, $account)
    {
        if (!$ids) {
            return [];
        }

        $output  = [];
        $results = $this->getEntityManager()->createQuery('
            SELECT s, t, u
            FROM DeskPRO:TwitterAccountStatus s
            INNER JOIN s.status t
            INNER JOIN t.user u
            WHERE s.status IN (?0)
                AND s.account = ?1
        ')->setParameters([$ids, $account])->execute();

        foreach ($results as $result) {
            $output[$result->status->id] = $result;
        }

        return $output;
    }

    public function getTimelineForAccount(TwitterAccountEntity $account, array $conditions = [], $sortByDate = 'DESC', $page = 1, $limit = self::DEFAULT_LIMIT)
    {
        $query = '
            SELECT s,
                a, action_agent, agent, agent_team, retweeted,
                t, u, ret, recip, long, in_reply
            FROM DeskPRO:TwitterAccountStatus s
            INNER JOIN s.account a
            LEFT JOIN s.action_agent action_agent
            LEFT JOIN s.agent agent
            LEFT JOIN s.agent_team agent_team
            LEFT JOIN s.retweeted retweeted
            INNER JOIN s.status t
            INNER JOIN t.user u
            LEFT JOIN t.retweet ret
            LEFT JOIN t.recipient recip
            LEFT JOIN t.long long
            LEFT JOIN t.in_reply_to_status in_reply
        ';

        list($where, $params) = $this->_getTimelineWhereClause($account, $conditions);

        $query .= sprintf(' %s ORDER BY s.date_created %s', $where, $this->normalizeSortByDate($sortByDate));

        $statuses = $this->getEntityManager()
            ->createQuery($query)
            ->setParameters($params)
            ->setMaxResults($limit)
            ->setFirstResult($this->calculateOffset($limit, $page))
            ->execute();

        return $statuses;
    }

    public function countTimelineForAccount(TwitterAccountEntity $account, array $conditions = [])
    {
        $query = '
            SELECT COUNT(s.id)
            FROM DeskPRO:TwitterAccountStatus s
            INNER JOIN s.status t
        ';

        list($where, $params) = $this->_getTimelineWhereClause($account, $conditions);

        $query .= ' '.$where;

        return $this->getEntityManager()->createQuery($query)->setParameters($params)->getSingleScalarResult();
    }

    protected function _getTimelineWhereClause(TwitterAccountEntity $account, array $conditions = [])
    {
        $query  = 'WHERE s.account = ?0';
        $params = [$account];
        $i      = 1;

        $type = isset($conditions['type']) ? $conditions['type'] : 'all';

        if ($type == 'sent') {
            $conditions['include_self']     = true;
            $conditions['include_archived'] = true; // can't actually archive sent statuses
        }

        $where = [];

        // note that sent should always be included - it will be filtered out above if needed
        switch ($type) {
            case 'timeline':
            case 'reply':
            case 'mention':
            case 'retweet':
            case 'direct':
            case 'sent':
                $where[] = "s.status_type = '$type'";
                break;

            case 'inbox':
                $where[] = "(s.status_type IN ('reply', 'mention', 'retweet', 'direct') OR s.is_favorited = 1)";
                break;

            case 'other':
                $where[] = "s.status_type NOT IN ('reply', 'mention', 'retweet', 'direct')";
                break;

            case 'favorite':
                $where[] = 's.is_favorited = true';
                break;

            case 'all':
            default:
                // nothing
        }

        if (empty($conditions['include_archived'])) {
            $where[] = 's.is_archived = 0';
        }

        if (isset($conditions['agent'])) {
            if ($conditions['agent'] === true) {
                $where[] = 's.agent IS NOT NULL';
            } elseif ($conditions['agent'] === false || $conditions['agent'] == '0') {
                $where[] = 's.agent IS NULL';
            } elseif ($conditions['agent']) {
                $conditions['agent'] = array_map('intval', (array) $conditions['agent']);
                $where[]             = 's.agent IN ('.implode(',', $conditions['agent']).')';
            }
        }
        if (isset($conditions['agent_team'])) {
            if ($conditions['agent_team'] === true) {
                $where[] = 's.agent_team IS NOT NULL';
            } elseif ($conditions['agent_team'] === false || $conditions['agent_team'] == '0') {
                $where[] = 's.agent_team IS NULL';
            } elseif ($conditions['agent_team']) {
                $conditions['agent_team'] = array_map('intval', (array) $conditions['agent_team']);
                $where[]                  = 's.agent_team IN ('.implode(',', $conditions['agent_team']).')';
            }
        }
        if (isset($conditions['assigned'])) {
            if ($conditions['assigned']) {
                $where[] = '(s.agent IS NOT NULL OR s.agent_team IS NOT NULL)';
            } else {
                $where[] = '(s.agent IS NULL AND s.agent_team IS NULL)';
            }
        }
        if (isset($conditions['favorited'])) {
            if ($conditions['favorited']) {
                $where[] = 's.is_favorited = true';
            } else {
                $where[] = 's.is_favorited = false';
            }
        }

        if (empty($conditions['include_self'])) {
            $where[]  = "(s.status_type <> 'direct' OR t.user <> ?$i)";
            $params[] = $account->user->getId();

            if ($where) {
                $query .= ' AND '.implode(' AND ', $where);
            }
        } else {
            $sent_condition = "(s.status_type = 'sent' OR (s.status_type = 'direct' AND t.user = ?$i))";
            $params[]       = $account->user->getId();

            if ($where) {
                $query .= ' AND (('.implode(' AND ', $where).") OR $sent_condition)";
            } else {
                $query .= " AND $sent_condition";
            }
        }

        return [$query, $params];
    }

    public function getSectionCounts($accounts)
    {
        $single = false;
        if ($accounts instanceof \Doctrine\Common\Collections\Collection) {
            $accounts = $accounts->toArray();
        } elseif (!is_array($accounts)) {
            $single   = $accounts->id;
            $accounts = [$accounts];
        }
        if (!$accounts) {
            return [];
        }

        if (count($accounts) == 1) {
            $account      = reset($accounts);
            $ids          = [$account->id];
            $dm_sent_case = ($account->user->id + 0);
        } else {
            $ids          = [];
            $dm_sent_case = 'CASE a.account_id';
            foreach ($accounts as $account) {
                $ids[] = $account->id;
                $dm_sent_case .= " WHEN $account->id THEN ".($account->user->id + 0);
            }
            $dm_sent_case .= ' END';
        }

        $person = App::getCurrentPerson();
        $person->loadHelper('AgentTeam');
        $team_ids = $person->getAgentTeamIds();
        $team_ids = ($team_ids ? implode(',', $team_ids) : '0');

        $person_id = $person->id;

        $results = App::getDb()->fetchAllKeyed("
            SELECT a.account_id,
                SUM(IF(a.agent_id = $person_id, 1, 0)) AS mine,
                SUM(IF(a.agent_team_id IN ($team_ids), 1, 0)) AS team,
                SUM(IF(a.agent_team_id IS NULL AND a.agent_id IS NULL AND (a.status_type IN ('mention', 'reply', 'retweet') OR (a.status_type = 'direct' AND s.user_id <> $dm_sent_case) OR a.is_favorited = 1), 1, 0)) AS unassigned,
                SUM(IF(a.status_type IN ('mention', 'reply', 'retweet') OR (a.status_type = 'direct' AND s.user_id <> $dm_sent_case) OR a.is_favorited = 1, 1, 0)) AS `all`
            FROM twitter_accounts_statuses AS a
            INNER JOIN twitter_statuses AS s ON (a.status_id = s.id)
            WHERE a.account_id IN (".implode(',', $ids).')
                AND a.is_archived = 0
            GROUP BY a.account_id
        ', [], 'account_id');

        if ($single) {
            return isset($results[$single]) ? $results[$single] : false;
        } else {
            return $results;
        }
    }

    public function getGroupedSectionCount(TwitterAccountEntity $account, $limit_type, $grouping)
    {
        $person = App::getCurrentPerson();

        $type_limit = " AND (a.status_type IN ('mention', 'reply', 'retweet') OR (a.status_type = 'direct' AND s.user_id <> ".$account->user->id.') OR a.is_favorited = 1)';

        switch ($limit_type) {
            case 'mine':
                $sql_condition = ' AND a.agent_id = '.$person->getId();
                break;

            case 'team':
                $person->loadHelper('AgentTeam');
                $teams = $person->getAgentTeamIds();
                if ($teams) {
                    $sql_condition = ' AND a.agent_team_id IN ('.implode(',', $teams).')';
                } else {
                    $sql_condition = ' AND 1=0';
                }
                break;

            case 'unassigned':
                $sql_condition = ' AND a.agent_id IS NULL AND a.agent_team_id IS NULL'.$type_limit;
                break;

            case 'all':
            default:
                $sql_condition = $type_limit;
        }

        if ($grouping == 'type') {
            $data = App::getDb()->fetchAllKeyValue("
                SELECT IF(a.status_type IN('direct', 'reply', 'mention', 'retweet'), a.status_type, 'other') AS id, COUNT(*) AS total
                FROM twitter_accounts_statuses AS a
                INNER JOIN twitter_statuses AS s ON (a.status_id = s.id)
                WHERE a.account_id = ? AND a.is_archived = 0 AND a.is_favorited = 0 $sql_condition
                GROUP BY IF(a.status_type IS NOT NULL, a.status_type, 'other')
            ", [$account->id]);

            $favorites = App::getDb()->fetchColumn("
                SELECT COUNT(*) AS total
                FROM twitter_accounts_statuses AS a
                INNER JOIN twitter_statuses AS s ON (a.status_id = s.id)
                WHERE a.account_id = ? AND a.is_archived = 0 AND a.is_favorited = 1 $sql_condition
            ", [$account->id]);
            if ($favorites) {
                $data['favorite'] = $favorites;
            }

            return $data;
        } elseif ($grouping == 'agent') {
            return App::getDb()->fetchAllKeyValue("
                SELECT a.agent_id AS id, COUNT(*) AS total
                FROM twitter_accounts_statuses AS a
                INNER JOIN twitter_statuses AS s ON (a.status_id = s.id)
                WHERE a.account_id = ? AND a.is_archived = 0 $sql_condition
                GROUP BY a.agent_id
            ", [$account->id]);
        } elseif ($grouping == 'team') {
            return App::getDb()->fetchAllKeyValue("
                SELECT a.agent_team_id AS id, COUNT(*) AS total
                FROM twitter_accounts_statuses AS a
                INNER JOIN twitter_statuses AS s ON (a.status_id = s.id)
                WHERE a.account_id = ? AND a.is_archived = 0 $sql_condition
                GROUP BY a.agent_team_id
            ", [$account->id]);
        } else {
            return [];
        }
    }

    /**
     * @param string $sortByDate (optional)
     *
     * @return string
     */
    protected function normalizeSortByDate($sortByDate = 'asc')
    {
        // check that sort by date is asc or desc
        if (!in_array(strtolower($sortByDate), ['asc', 'desc'])) {
            $sortByDate = 'asc';
        }

        return strtoupper($sortByDate);
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return int
     */
    protected function calculateOffset($limit, $page)
    {
        if ($page < 1) {
            $page = 1;
        }

        return ($page - 1) * $limit;
    }
}
