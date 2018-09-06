<?php

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketTerms;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Class TicketSearch.
 */
class TicketSearch extends SearcherAbstract
{
    const TERM_ID                    = 'id';
    const TERM_RANGE_ID              = 'range_id';
    const TERM_REF                   = 'ref';
    const TERM_PERSON_ID             = 'person_id';
    const TERM_PERSON_RANGE_ID       = 'person_range_id';
    const TERM_DEPARTMENT            = 'department';
    const TERM_CATEGORY              = 'category';
    const TERM_PRODUCT               = 'product';
    const TERM_AGENT                 = 'agent';
    const TERM_AGENT_TEAM            = 'agent_team';
    const TERM_STATUS                = 'status';
    const TERM_HIDDEN_STATUS         = 'hidden_status';
    const TERM_EMAIL_ACCOUNT         = 'email_account';
    const TERM_WORKFLOW              = 'workflow';
    const TERM_PRIORITY              = 'priority';
    const TERM_SUBJECT               = 'subject';
    const TERM_SUBJECT_ADV           = 'subject_adv';
    const TERM_MESSAGE               = 'ticket_message';
    const TERM_MESSAGE_ADV           = 'ticket_message_adv';
    const TERM_ORGANIZATION          = 'organization';
    const TERM_LANGUAGE              = 'language';
    const TERM_PARTICIPANT           = 'participant';
    const TERM_PERSON                = 'person';
    const TERM_LABEL                 = 'label';
    const TERM_TICKET_FIELD          = 'ticket_field';
    const TERM_DATE_CREATED          = 'date_created';
    const TERM_DATE_RESOLVED         = 'date_resolved';
    const TERM_DATE_ARCHIVED         = 'date_archived';
    const TERM_DATE_STATUS           = 'date_status';
    const TERM_DATE_LAST_USER_REPLY  = 'date_last_user_reply';
    const TERM_DATE_LAST_AGENT_REPLY = 'date_last_agent_reply';
    const TERM_DATE_LAST_REPLY       = 'date_last_reply';
    const TERM_DATE_ON_HOLD          = 'date_on_hold';
    const TERM_URGENCY               = 'urgency';
    const TERM_USER_WAITING          = 'user_waiting';
    const TERM_TOTAL_USER_WAITING    = 'total_user_waiting';
    const TERM_AGENT_WAITING         = 'agent_waiting';
    const TERM_ARCHIVE_SEARCH        = 'archive_search';
    const TERM_DELETED               = 'deleted';
    const TERM_CREATION_SYSTEM       = 'creation_system';
    const TERM_HOLD                  = 'is_hold';
    const TERM_FLAGGED               = 'flagged';
    const TERM_TEXT                  = 'text';
    const TERM_SENT_TO_ADDRESS       = 'sent_to_address';
    const TERM_DAY_CREATED           = 'day_created';
    const TERM_FEEDBACK_RATING       = 'feedback_rating';
    const TERM_FEEDBACK_LINKS        = 'feedback_links';
    const TERM_SLA                   = 'sla';
    const TERM_SLA_STATUS            = 'sla_status';
    const TERM_SLA_COMPLETED         = 'sla_completed';
    const TERM_IP_ADDRESS            = 'ip_address';
    const TERM_PROBLEMS              = 'problems';
    const TERM_BRAND                 = 'brand';

    /**
     * True to search in the non-search tables (aka all tickets not just active).
     *
     * @var bool
     */
    protected $is_archive = false;

    /**
     * @var PersonSearch
     */
    protected $person_search = null;

    /**
     * @var OrganizationSearch
     */
    protected $org_search = null;

    /**
     * From getSqlParts().
     *
     * @var array
     */
    protected $sql_parts = null;

    /**
     * Summary of terms in phrases.
     *
     * @var array
     */
    protected $summary = [];

    /**
     * Summary of sorting in phrases.
     *
     * @var array
     */
    protected $order_summary = [];

    /**
     * An array of fields these search terms are affected by.
     * Used in ListUpdater to determine if a filter needs changing on the client.
     *
     * @var array
     */
    protected $affected_fields      = [];
    protected $done_affected_fields = false;

    /**
     * An array of search terms that are specific, as in only allow a single
     * value (so not ranges or IN() types). For example, a single department or organization.
     *
     * @return array
     */
    protected $specific_fields = [];

    /**
     * Amount to limit results by (unless pageinfo is provided).
     *
     * @var string
     */
    protected $limit = '10000';

    /**
     * @var array
     */
    protected $add_raw_wheres = [];

    /**
     * @var array
     */
    protected $add_raw_joins = [];

    /**
     * @var array
     */
    protected $add_raw_selects = [];

    /**
     * @var bool
     */
    protected $is_filter_search = false;

    /**
     * @var bool
     */
    protected $done_person_context_check = false;

    /**
     * @var string
     */
    protected $queryNote = false;

    /**
     * @var null|string
     */
    public $_last_sql = null;

    /**
     * Set a set of person search terms.
     *
     * @param PersonSearch $person_search
     */
    public function setPersonSearch(PersonSearch $person_search)
    {
        $person_search->setMode(PersonSearch::MODE_ANY);
        $this->person_search = $person_search;
        if ($this->person) {
            $this->person_search->setPerson($this->person);
        }
    }

    /**
     * @param Entity\Person $person
     */
    public function setPersonContext(Entity\Person $person = null)
    {
        $this->person = $person;
        if ($this->person_search) {
            $this->person_search->setPerson($person);
        }
    }

    /**
     * Set a note on the query that will get run. This adds the note in a comment at the top of the query
     * to aid in debugging (e.g. slow query logs, process list, etc).
     *
     * @param string $queryNote
     */
    public function setQueryNote($queryNote)
    {
        $this->queryNote = $queryNote;
    }

    /**
     * Set a set of org search terms.
     *
     * @param OrganizationSearch $org_search
     */
    public function setOrganizationSearch(OrganizationSearch $org_search)
    {
        $this->org_search = $org_search;
    }

    /**
     * Search old (archived) tickets that are archived (aka not in the search tables).
     */
    public function enableArchiveSearch()
    {
        $this->is_archive = true;
    }

    /**
     * Enables only non-hidden or archived tickets.
     */
    public function enableFilterSearch()
    {
        $this->is_filter_search = true;
    }

    /**
     * Are we using archive mode?
     *
     * @return bool
     */
    public function isArchiveSearch()
    {
        return $this->is_archive;
    }

    /**
     * Set the amount to limit results by (by default).
     *
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Add a new term.
     *
     * @param string $term
     * @param  $op
     * @param array $data
     *
     * @return $this
     */
    public function addTerm($term, $op, $data)
    {
        parent::addTerm($term, $op, $data);

        if ($this->isArchiveTerm($term, $op, $data)) {
            $this->is_archive = true;
        }

        return $this;
    }

    /**
     * Add a new term.
     *
     * @param string $term
     * @param  $op
     * @param array $data
     *
     * @return $this
     */
    public function addAnyTerm($term, $op, $data)
    {
        parent::addAnyTerm($term, $op, $data);

        if ($this->isArchiveTerm($term, $op, $data)) {
            $this->is_archive = true;
        }

        return $this;
    }

    /**
     * @param string $term
     * @param array  $data
     *
     * @return bool
     */
    public function isArchiveTerm($term, $op, $data)
    {
        if (!$this->is_archive && $term == self::TERM_STATUS) {
            if (is_array($data) and count($data) == 1) {
                $data = Arrays::getFirstItem($data);
            }
            if (!is_array($data)) {
                $data = [$data];
            }

            foreach ($data as $s) {
                if ($s == 'archived' || strpos($s, 'hidden') === 0) {
                    return true;
                }
            }
        }
        if (!$this->is_archive && $term == self::TERM_HIDDEN_STATUS) {
            return true;
        }
        if (!$this->is_archive && $term == self::TERM_DELETED) {
            return true;
        }

        if ($term === self::TERM_DATE_ARCHIVED) {
            return true;
        }

        return false;
    }

    /**
     * Get the summary of crtiera.
     *
     * @return array
     */
    public function getSummary()
    {
        $this->getSqlParts();

        $summary = $this->summary;
        if ($this->person_search) {
            $person_summary = $this->person_search->getSummary();
            if ($person_summary) {
                $summary = array_merge($summary, $person_summary);
            }
        }
        if ($this->org_search) {
            $org_summary = $this->org_search->getSummary();
            if ($org_summary) {
                $summary = array_merge($summary, $org_summary);
            }
        }

        return $summary;
    }

    /**
     * Get the order-by summary.
     *
     * @return array
     */
    public function getOrderBySummary()
    {
        $this->getOrderByPart();

        return $this->order_summary;
    }

    /**
     * Get specific fields in this search.
     *
     * @return array
     */
    public function getSpecificFields()
    {
        $this->getSqlParts();

        return $this->specific_fields;
    }

    /**
     * @return array
     */
    public function getAffectedFields()
    {
        if ($this->done_affected_fields) {
            return $this->affected_fields;
        }

        $this->done_affected_fields = true;
        $this->affected_fields      = [];

        foreach ([['all', $this->terms], ['any', $this->terms_any]] as $term_set) {
            foreach ($term_set[1] as $info) {
                if (!$info || !is_array($info)) {
                    continue;
                }
                list($term_string, $op, $choice, $term, $term_id) = $info;

                if (!$term) {
                    continue;
                }

                switch ($term) {
                    case self::TERM_ID:
                    case self::TERM_RANGE_ID:
                        break;
                    case self::TERM_REF:
                        break;
                    case self::TERM_PERSON_ID:
                    case self::TERM_PERSON_RANGE_ID:
                        break;
                    case self::TERM_ARCHIVE_SEARCH:
                        break;
                    case self::TERM_DEPARTMENT:
                        $this->affected_fields[] = 'ticket.department_id';
                        break;
                    case self::TERM_EMAIL_ACCOUNT:
                        $this->affected_fields[] = 'ticket.email_account_id';
                        break;
                    case self::TERM_DELETED:
                        $this->affected_fields[] = 'ticket.status';
                        $this->affected_fields[] = 'ticket.hidden_status';
                        break;
                    case self::TERM_CATEGORY:
                        $this->affected_fields[] = 'ticket.category_id';
                        break;
                    case self::TERM_PRODUCT:
                        $this->affected_fields[] = 'ticket.product_id';
                        break;
                    case self::TERM_PRIORITY:
                        $this->affected_fields[] = 'ticket.priority_id';
                        break;
                    case self::TERM_URGENCY:
                        $this->affected_fields[] = 'ticket.urgency';
                        break;
                    case self::TERM_DATE_CREATED:
                        break;
                    case self::TERM_DATE_STATUS:
                        break;
                    case self::TERM_DATE_RESOLVED:
                        $this->affected_fields[] = 'ticket.date_resolved';
                        break;
                    case self::TERM_DATE_ON_HOLD:
                        $this->affected_fields[] = 'ticket.date_on_hold';
                        break;
                    case self::TERM_DATE_ARCHIVED:
                        $this->affected_fields[] = 'ticket.date_archived';
                        break;
                    case self::TERM_DATE_LAST_USER_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_user_reply';
                        break;
                    case self::TERM_DATE_LAST_AGENT_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_agent_reply';
                        break;
                    case self::TERM_DATE_LAST_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_reply';
                        break;
                    case self::TERM_WORKFLOW:
                        $this->affected_fields[] = 'ticket.workflow_id';
                        break;
                    case self::TERM_FEEDBACK_RATING:
                        break;
                    case self::TERM_SLA:
                        $this->affected_fields[] = 'ticket.sla_id';
                        break;
                    case self::TERM_SLA_STATUS:
                        break;
                    case self::TERM_SLA_COMPLETED:
                        $this->affected_fields[] = 'ticket.sla_completed';
                        $this->affected_fields[] = 'ticket.sla_id';
                        break;
                    case self::TERM_LANGUAGE:
                        $this->affected_fields[] = 'ticket.language_id';
                        break;
                    case self::TERM_AGENT:
                        $this->affected_fields[] = 'ticket.agent_id';
                        break;
                    case self::TERM_AGENT_TEAM:
                        $this->affected_fields[] = 'ticket.agent_team_id';
                        break;
                    case self::TERM_STATUS:
                        $this->affected_fields[] = 'ticket.status';
                        break;
                    case self::TERM_HIDDEN_STATUS:
                        $this->affected_fields[] = 'ticket.hidden_status';
                        break;
                    case self::TERM_HOLD:
                        $this->affected_fields[] = 'ticket.is_hold';
                        break;
                    case self::TERM_ORGANIZATION:
                        $this->affected_fields[] = 'ticket.organization_id';
                        break;
                    case self::TERM_PARTICIPANT:
                        $this->affected_fields[] = 'ticket.participants';
                        break;
                    case self::TERM_PERSON:
                        $this->affected_fields[] = 'ticket.person_id';
                        break;
                    case self::TERM_IP_ADDRESS:
                        break;
                    case self::TERM_SUBJECT:
                        $this->affected_fields[] = 'ticket.subject';
                        break;
                    case self::TERM_SUBJECT_ADV:
                        $this->affected_fields[] = 'ticket.subject';
                        break;
                    case self::TERM_MESSAGE:
                        $this->affected_fields[] = 'ticket.message';
                        break;
                    case self::TERM_MESSAGE_ADV:
                        $this->affected_fields[] = 'ticket.message';
                        break;
                    case self::TERM_FLAGGED:
                        $this->affected_fields[] = 'tickets_flagged';
                        break;
                    case self::TERM_LABEL:
                        $this->affected_fields[] = 'ticket.labels';
                        break;
                    case self::TERM_TICKET_FIELD:
                        $this->affected_fields[] = 'ticket.custom_data_ticket_'.$term_id;
                        break;
                    case 'time_waiting':
                    case self::TERM_USER_WAITING:
                        $this->affected_fields[] = 'ticket.date_user_waiting';
                        break;
                    case self::TERM_AGENT_WAITING:
                        $this->affected_fields[] = 'ticket.date_agent_waiting';
                        break;
                    case self::TERM_TOTAL_USER_WAITING:
                        $this->affected_fields[] = 'ticket.total_user_waiting';
                        break;
                    case self::TERM_CREATION_SYSTEM:
                        break;
                    case 'escalation_eliminator':
                        break;
                    case 'time_created':
                    case 'time_last_user_reply':
                        break;
                    case self::TERM_PROBLEMS:
                        $this->affected_fields[] = 'ticket.problems';
                        break;
                    case self::TERM_DAY_CREATED:
                        break;
                }
            }
        }

        $this->affected_fields = array_unique($this->affected_fields, SORT_STRING);

        return $this->affected_fields;
    }

    /**
     * (Optimised for this class, doesnt build query like usual).
     *
     * @return bool
     */
    public function needsPersonContext()
    {
        if ($this->done_person_context_check) {
            return $this->used_person_context > 0;
        }

        if ($this->done_person_context_check) {
            return $this->used_person_context;
        }

        $this->used_person_context       = 0;
        $this->done_person_context_check = true;

        foreach ([['all', $this->terms], ['any', $this->terms_any]] as $term_set) {
            foreach ($term_set[1] as $info) {
                if (!$info || !is_array($info)) {
                    continue;
                }
                list($term_string, $op, $choice, $term, $term_id) = $info;

                if (!$term) {
                    continue;
                }

                switch ($term) {
                    case self::TERM_AGENT:
                        $res = $this->_normalizeAgentChoice($choice);
                        if ($res['has_dyn']) {
                            $this->used_person_context = true;
                        }
                        break;
                    case self::TERM_PARTICIPANT:
                        $res = $this->_normalizeAgentChoice($choice);
                        if ($res['has_dyn']) {
                            $this->used_person_context = true;
                        }
                        break;
                    case self::TERM_AGENT_TEAM:
                        $res = $this->_normalizeAgentTeamChoice($choice);
                        if ($res['has_dyn']) {
                            $this->used_person_context = true;
                        }
                        break;
                }
            }
        }

        return (bool) $this->used_person_context;
    }

    /**
     * Check an array of fields to see if this searcher has any of them.
     *
     * @param array $fields
     *
     * @return bool
     */
    public function hasAnyAffectedFields(array $fields)
    {
        return array_intersect($fields, $this->getAffectedFields());
    }

    /**
     * Run the search and return an array of matching ID's.
     *
     * @param array $page_info
     *
     * @return array
     */
    public function getMatches(array $page_info = null)
    {
        $sql = $this->getSql($page_info);
        $this->getLogger()->logDebug('Search Query: '.$sql);
        $time = microtime(true);

        $db = App::getDbRead('search.filter.tickets', ['query' => $sql]);

        try {
            $ticket_ids = $db->fetchAllCol($sql);
        } catch (\PDOException $e) {
            $ticket_ids = [];
            SystemErrorHandler::logException($e, true);

            if (defined('DP_DEBUG') && DP_DEBUG) {
                throw $e;
            }
        }

        $this->getLogger()->logDebug('-- Time: '.sprintf('%.5f', microtime(true) - $time));
        $this->getLogger()->logDebug('-- Count: '.count($ticket_ids));
        $this->getLogger()->logDebug('-- IDs: '.implode(', ', $ticket_ids));

        return $ticket_ids;
    }

    /**
     * Run the search and get the count.
     */
    public function getCount()
    {
        $ticket_parts = $this->getSqlParts();
        $user_parts   = null;
        $org_parts    = null;
        if ($this->person_search) {
            $user_parts = $this->person_search->getSqlParts();
        }
        if ($this->org_search) {
            $org_parts = $this->org_search->getSqlParts();
        }

        if ($this->isArchiveSearch() || !App::getSetting('core_tickets.use_archive')) {
            $table = 'tickets';
        } else {
            $table = 'tickets_search_active';
        }

        //------------------------------
        // Standard for permissions
        //------------------------------

        $with_part_union = false;

        if ($this->person and $this->person['is_agent']) {
            $assigned_perm_part = "tickets.agent_id = {$this->person['id']}";
            if ($this->person->getHelper('getagentteamids')->getAgentTeamIds()) {
                $assigned_perm_part = "($assigned_perm_part OR tickets.agent_team_id IN (".implode(',', $this->person->getHelper('getagentteamids')->getAgentTeamIds()).'))';
            }

            $where_perm = [];

            // Cant view anything else:
            // -> No departments
            // -> Or if you cant view unassigned and cant view others, then that leaves nothing (the 'always' perm above will get your own still)
            if (!$this->person->getAllowedDepartments() || (!$this->person->hasPerm('agent_tickets.view_unassigned') && !$this->person->hasPerm('agent_tickets.view_others'))) {
                $where_perm[] = '0';
            } else {
                if ($this->person->getDisallowedDepartments()) {
                    $where_perm[] = '(tickets.department_id NOT IN ('.implode(',', $this->person->getDisallowedDepartments()).') OR tickets.department_id IS NULL)';
                }

                if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
                    $where_perm[] = '(tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)';
                }

                if (!$this->person->hasPerm('agent_tickets.view_others')) {
                    $where_perm[] = 'tickets.agent_id IS NULL';
                    $where_perm[] = 'tickets.agent_team_id IS NULL';
                }
            }

            if ($where_perm) {
                $where_perm = '('.$assigned_perm_part.' OR ('.implode(' AND ', $where_perm).'))';
            } else {
                $where_perm = '';
            }

            $with_part_union = true;
        } else {
            $where_perm = '';
        }

        if ($where_perm) {
            $where_perm .= ' AND ';
        }

        //------------------------------
        // Add joins
        //------------------------------

        $sql_joins = '';

        foreach ($ticket_parts['joins'] as $j) {
            if (is_array($j)) {
                $sql_joins .= $j[1].' ';
            } else {
                throw new \RuntimeException('array expected');
            }
        }

        if ($user_parts) {
            $sql_joins .= 'LEFT JOIN people ON (people.id = tickets.person_id) ';
        }
        if ($org_parts) {
            $sql_joins .= 'LEFT JOIN organizations ON (organizations.id = tickets.organization_id) ';
        }

        if ($user_parts and $user_parts['joins']) {
            foreach ($user_parts['joins'] as $j) {
                if (is_array($j)) {
                    $sql_joins .= $j[1].' ';
                } else {
                    $sql_joins .= "LEFT JOIN $j ON $j.person_id = people.id ";
                }
            }
        }

        if ($org_parts and $org_parts['joins']) {
            foreach ($org_parts['joins'] as $j) {
                if (is_array($j)) {
                    $sql_joins .= $j[1].' ';
                } else {
                    $sql_joins .= "LEFT JOIN $j ON $j.organization_id = organizations.id ";
                }
            }
        }

        if ($this->add_raw_joins) {
            $sql_joins .= implode(' ', $this->add_raw_joins);
        }

        //------------------------------
        // Add wheres
        //------------------------------

        $where = '1';

        if (!empty($ticket_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $ticket_parts['wheres']);
        }
        if (!empty($ticket_parts['wheres_any'])) {
            $where .= 'AND (';
            $where .= implode(' OR ', $ticket_parts['wheres_any']);
            if (!empty($user_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $user_parts['wheres_any']);
            }
            if (!empty($org_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $org_parts['wheres_any']);
            }
            $where .= ')';
        } elseif (!empty($user_parts['wheres_any']) || !empty($org_parts['wheres_any'])) {
            $where .= 'AND (';
            if (!empty($user_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $user_parts['wheres_any']);
            }
            if (!empty($org_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $org_parts['wheres_any']);
            }
            $where .= ')';
        }
        if (!empty($user_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $user_parts['wheres']);
        }
        if (!empty($org_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $org_parts['wheres']);
        }

        if ($this->add_raw_wheres) {
            $where .= ' AND '.implode(' AND ', $this->add_raw_wheres);
        }

        if ($this->is_filter_search && !$this->is_archive) {
            $where .= " AND tickets.status NOT IN ('archived', 'hidden') ";
        }

        $sql  = "SELECT tickets.id AS ticket_id FROM $table AS tickets ";
        $sql2 = "SELECT tickets.id AS ticket_id FROM tickets_participants AS part_perm LEFT JOIN $table AS tickets ON (tickets.id = part_perm.ticket_id) ";

        if (!$with_part_union) {
            $sql = "SELECT COUNT(DISTINCT tickets.id) FROM $table AS tickets ";
        }

        $sql .= " $sql_joins ";
        $sql2 .= " $sql_joins ";

        $sql .= " WHERE $where_perm $where";
        $sql2 .= " WHERE $where ";
        if ($this->person) {
            $sql2 .= " AND part_perm.person_id = {$this->person->getId()} ";
        }

        $sql .= ' LIMIT 10000 ';
        $sql2 .= ' LIMIT 10000 ';

        if ($with_part_union) {
            $count_sql = "
                SELECT COUNT(DISTINCT ticket_id)
                FROM (
                    ($sql)
                    UNION
                    ($sql2)
                ) a
            ";
        } else {
            $count_sql = $sql;
        }

        if ($this->queryNote) {
            $count_sql = "/*{$this->queryNote}*/ $count_sql";
        }

        $this->getLogger()->logDebug('Search Count Query: '.$count_sql);
        $time = microtime(true);

        $db = App::getDbRead('search.filter.tickets', ['query' => $count_sql]);

        try {
            $result = $db->fetchColumn($count_sql);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $result = 0;
            SystemErrorHandler::logException($e, true);

            if (defined('DP_DEBUG') && DP_DEBUG) {
                throw $e;
            }
        }

        $this->getLogger()->logDebug('-- Time: '.sprintf('%.5f', microtime(true) - $time));
        $this->getLogger()->logDebug('-- Count: '.$result);

        return $result;
    }

    /**
     * Get the SQL query that'll fetch the results.
     *
     * @param array $page_info
     *
     * @return string
     */
    public function getSql(array $page_info = null)
    {
        $ticket_parts = $this->getSqlParts();
        $user_parts   = null;
        $org_parts    = null;
        if ($this->person_search) {
            $user_parts = $this->person_search->getSqlParts();
        }
        if ($this->org_search) {
            $org_parts = $this->org_search->getSqlParts();
        }

        $order_by = $this->getOrderByPart();

        if ($this->isArchiveSearch() || !App::getSetting('core_tickets.use_archive')) {
            $table = 'tickets';
        } else {
            $table = 'tickets_search_active';
        }

        $select = '';
        if ($this->add_raw_selects) {
            $select = ', '.implode(', ', $this->add_raw_selects);
        }
        $sql  = "SELECT tickets.id $select FROM $table AS tickets ";
        $sql2 = "SELECT part_perm.ticket_id AS id $select FROM tickets_participants AS part_perm LEFT JOIN $table AS tickets ON (tickets.id = part_perm.ticket_id) ";

        //------------------------------
        // Standard for permissions
        //------------------------------

        $with_part_union = false;

        /*
         * Permission resolution is like:
         * (ALWAYS OWN) OR (
         *     NOT IN DISALLOWED DEPS
         *     AND <if not view unassigned: IS NOT UNASSIGNED>
         *     AND <if not view others: IS UNASSIGNED>
         * )
         */

        if ($this->person and $this->person['is_agent']) {
            $assigned_perm_part = "tickets.agent_id = {$this->person['id']}";
            if ($this->person->getTeamIds()) {
                $assigned_perm_part = "($assigned_perm_part OR tickets.agent_team_id IN (".implode(',', $this->person->getTeamIds()).'))';
            }

            $where_perm = [];

            // Cant view anything else:
            // -> No departments
            // -> Or if you cant view unassigned and cant view others, then that leaves nothing (the 'always' perm above will get your own still)
            if (!$this->person->getAllowedDepartments('tickets', true) || (!$this->person->hasPerm('agent_tickets.view_unassigned') && !$this->person->hasPerm('agent_tickets.view_others'))) {
                $where_perm[] = '0';
            } else {
                $disAllowedDepartments = $this->person->getDisallowedDepartments('tickets', true);
                if ($disAllowedDepartments) {
                    $where_perm[] = '(tickets.department_id NOT IN ('.implode(',', $disAllowedDepartments).') OR tickets.department_id IS NULL)';
                }

                if (!$this->person->hasPerm('agent_tickets.view_unassigned')) {
                    $where_perm[] = '(tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)';
                }

                if (!$this->person->hasPerm('agent_tickets.view_others')) {
                    $where_perm[] = 'tickets.agent_id IS NULL';
                    $where_perm[] = 'tickets.agent_team_id IS NULL';
                }
            }

            if ($where_perm) {
                $where_perm = '('.$assigned_perm_part.' OR ('.implode(' AND ', $where_perm).'))';
            } else {
                $where_perm = '';
            }

            $with_part_union = true;
        } else {
            $where_perm = '';
        }

        if ($where_perm) {
            $where_perm .= ' AND ';
        }

        //------------------------------
        // Add joins
        //------------------------------

        $sql_joins = '';

        foreach ($ticket_parts['joins'] as $j) {
            if (is_array($j)) {
                $sql_joins .= $j[1].' ';
            } else {
                throw new \RuntimeException('array expected');
            }
        }

        if ($user_parts) {
            $sql_joins .= 'LEFT JOIN people ON (people.id = tickets.person_id) ';
        }
        if ($org_parts) {
            $sql_joins .= 'LEFT JOIN organizations ON (organizations.id = tickets.organization_id) ';
        }

        if ($user_parts and $user_parts['joins']) {
            foreach ($user_parts['joins'] as $j) {
                if (is_array($j)) {
                    $sql_joins .= $j[1].' ';
                } else {
                    $sql_joins .= "LEFT JOIN $j ON $j.person_id = people.id ";
                }
            }
        }

        if ($org_parts and $org_parts['joins']) {
            foreach ($org_parts['joins'] as $j) {
                if (is_array($j)) {
                    $sql_joins .= $j[1].' ';
                } else {
                    $sql_joins .= "LEFT JOIN $j ON $j.organization_id = organizations.id ";
                }
            }
        }

        if (is_array($order_by)) {
            $order_join = $order_by[0];
            $order_by   = $order_by[1];

            $sql_joins .= " $order_join ";
        }

        if ($this->add_raw_joins) {
            $sql_joins .= implode(' ', $this->add_raw_joins);
        }

        //------------------------------
        // Add wheres
        //------------------------------

        $where = '1';

        if (!empty($ticket_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $ticket_parts['wheres']);
        }
        if (!empty($ticket_parts['wheres_any'])) {
            $where .= ' AND (';
            $where .= implode(' OR ', $ticket_parts['wheres_any']);
            if (!empty($user_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $user_parts['wheres_any']);
            }
            if (!empty($org_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $org_parts['wheres_any']);
            }
            $where .= ')';
        } elseif (!empty($user_parts['wheres_any']) || !empty($org_parts['wheres_any'])) {
            $where .= ' AND (';
            $where .= '0';
            if (!empty($user_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $user_parts['wheres_any']);
            }
            if (!empty($org_parts['wheres_any'])) {
                $where .= ' OR '.implode(' OR ', $org_parts['wheres_any']);
            }
            $where .= ')';
        }
        if (!empty($user_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $user_parts['wheres']);
        }
        if (!empty($org_parts['wheres'])) {
            $where .= ' AND '.implode(' AND ', $org_parts['wheres']);
        }

        if ($this->add_raw_wheres) {
            $where .= ' AND '.implode(' AND ', $this->add_raw_wheres);
        }

        if ($this->is_filter_search && !$this->is_archive) {
            $where .= " AND tickets.status NOT IN ('archived', 'hidden') ";
        }

        $sql .= " $sql_joins WHERE $where_perm $where";
        $sql2 .= " $sql_joins WHERE $where";

        if ($this->person) {
            $sql2 .= " AND part_perm.person_id = {$this->person->getId()} ";
        }

        $limit_sql = '';
        if ($page_info) {
            // A null limit means no limit :o
            if ($page_info['limit'] !== null) {
                $limit_sql = " LIMIT {$page_info['offset']}, {$page_info['limit']} ";
            }
        } else {
            if ($this->limit) {
                $limit_sql = ' LIMIT '.$this->limit;
            }
        }

        // each query must have a limit here even if its used in the union
        // otherwise a big db and/or queries against tickets (instead of tickets_search_active)
        // will have huge result and kill the server
        $sql .= " GROUP BY tickets.id $order_by $limit_sql";
        $sql2 .= " GROUP BY part_perm.id $order_by $limit_sql ";

        if ($with_part_union) {
            $unionLimit  = !empty($page_info['limit']) ? $page_info['limit'] : $this->limit;
            $selectQuery = "
                ($sql)
                UNION
                ($sql2)
                $order_by
            ".($unionLimit ? "LIMIT {$unionLimit}" : '');
        } else {
            $selectQuery = $sql;
        }

        if ($this->queryNote) {
            $selectQuery = "/*{$this->queryNote}*/ $selectQuery";
        }

        $this->_last_sql = $selectQuery;

        return $selectQuery;
    }

    /**
     * Get the ORDER BY clause based on order info set.
     *
     * @return string
     */
    public function getOrderByPart()
    {
        if (!$this->order_by and $this->person_search and $this->person_search->getOrderBy()) {
            return $this->person_search->getOrderByPart();
        }

        // Set a default if none
        if (!$this->order_by) {
            $this->order_by = ['ticket.date_created', 'DESC'];
        }

        list($type, $dir) = $this->order_by;

        $dir = strtoupper($dir);
        if ($dir != self::ORDER_ASC and $dir != self::ORDER_DESC) {
            $dir = self::ORDER_DESC;
        }

        $r_dir = $dir == self::ORDER_ASC ? 'DESC' : 'ASC';

        $term_id = null;
        $m       = null;
        if (preg_match('#^(.*?)\[(.*?)\]$#', $type, $m)) {
            $type    = $m[1];
            $term_id = $m[2];
        }

        $order_by = '';
        $tr       = App::get('language_manager');

        switch ($type) {
            case 'ticket.urgency':
                $statuses = $this->getApplicableStatuses();

                // urgency only applies to awaiting_agnet
                if (in_array('awaiting_agent', $statuses)) {
                    // only have awaiting agent
                    if (count($statuses) === 1) {
                        $this->add_raw_selects[] = 'tickets.urgency AS tickets_urgency';
                        $order_by                = "ORDER BY tickets_urgency $dir, id $r_dir";

                    // a mix of stautses, so we need to compute it
                    } else {
                        $this->add_raw_selects[] = "IF(tickets.status = 'awaiting_agent', tickets.urgency, IF(tickets.status = 'awaiting_user', 1, 0)) AS status_order";
                        $order_by                = "ORDER BY status_order $dir, id $r_dir";
                    }
                } else {
                    // urgency does not apply to other statuses
                    $order_by = "ORDER BY id $dir";
                }

                $this->order_summary = $tr->phrase('agent.general.urgency');
                break;

            case 'ticket.status':
                $this->add_raw_selects[] = "
                    CASE WHEN tickets.status =  'awaiting_agent' THEN 1
                    WHEN tickets.status =  'awaiting_user' THEN 2
                    WHEN tickets.status =  'resolved' THEN 3
                    WHEN tickets.status =  'archived' THEN 4
                    ELSE 3
                    END AS status_order
                ";

                $order_by = "ORDER BY status_order $dir, tickets.urgency DESC";
                break;

            case 'ticket.date_created':
                $order_by            = "ORDER BY id $dir";
                $this->order_summary = $tr->phrase('agent.general.date_created');
                break;

            case 'ticket.priority':
                $pris = App::getEntityRepository('DeskPRO:TicketPriority')->getIdsInOrder();
                if ($pris) {
                    $this->add_raw_selects[] = 'FIELD(tickets.priority_id, '.implode(',', $pris).') AS status_order';
                    $order_by                = "ORDER BY status_order $dir, id $r_dir";
                } else {
                    $order_by = "ORDER BY id $dir";
                }
                $this->order_summary = $tr->phrase('agent.general.priority');
                break;

            case 'ticket.sla_severity':
                $this->add_raw_selects[] = "MAX(FIELD(sort_table.sla_status, 'ok', 'warning', 'fail')) AS status_order";
                $this->add_raw_selects[] = "IF(MAX(FIELD(sort_table.sla_status, 'ok', 'warning', 'fail')) <= 1, MIN(sort_table.warn_date), MIN(sort_table.fail_date)) AS status_order2";
                $this->order_summary     = 'SLA Severity';
                $order_by                = [
                    'INNER JOIN ticket_slas AS sort_table ON (sort_table.ticket_id = tickets.id)',
                    "ORDER BY status_order $dir, status_order2 $dir",
                ];
                break;

            case 'ticket.date_resolved':
                $this->add_raw_selects[] = 'tickets.date_resolved AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.date_resolved');
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.date_archived':
                $this->add_raw_selects[] = 'tickets.date_archived AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.date_opened');
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.last_activity':
                $this->add_raw_selects[] = 'tickets.date_last_user_reply AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.date_of_last_user_reply');
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.total_user_waiting':
                $this->add_raw_selects[] = 'tickets.total_user_waiting AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.total_time_waiting');
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.date_user_waiting':
                $this->add_raw_selects[] = 'tickets.date_user_waiting AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.time_waiting');
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.brand':
                $this->add_raw_selects[] = 'sort_table.name AS brand_order';
                $this->order_summary     = $tr->phrase('agent.general.brand');
                $order_by                = [
                    'INNER JOIN brands AS sort_table ON (sort_table.id = tickets.brand_id)',
                    "ORDER BY brand_order $dir, id DESC",
                ];
                break;

            case 'ticket.organization':
                $this->add_raw_selects[] = 'sort_table.name AS status_order';
                $this->order_summary     = $tr->phrase('agent.general.organization_name');
                $order_by                = [
                    'INNER JOIN organizations AS sort_table ON (sort_table.id = tickets.organization_id)',
                    "ORDER BY status_order $dir, id DESC",
                ];
                break;

            case 'ticket.date_last_user_reply':
                $this->add_raw_selects[] = 'tickets.date_last_user_reply AS status_order';
                $this->order_summary     = 'Date of Last User Reply';
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.date_last_agent_reply':
                $this->order_summary     = 'Date of Last Agent Reply';
                $this->add_raw_selects[] = 'tickets.date_last_agent_reply AS status_order';
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.date_last_reply':
                $this->order_summary     = 'Date of Last Reply';
                $this->add_raw_selects[] = "GREATEST(COALESCE(tickets.date_last_agent_reply, '0000-00-00'), COALESCE(tickets.date_last_user_reply, '0000-00-00'), tickets.date_created) AS status_order";
                $order_by                = "ORDER BY status_order $dir, id DESC";
                break;

            case 'ticket.ticket_field':
                $field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
                if (!$field) {
                    break;
                }

                $this->order_summary = $field['title'];

                $search_type = $field->getHandler()->getSearchType();

                switch ($search_type) {
                    case 'input':
                    case 'value':
                        $this->add_raw_selects[] = "sort_table.$search_type AS status_order";
                        $order_by                = [
                            "JOIN custom_data_ticket AS sort_table ON (sort_table.ticket_id = tickets.id AND sort_table.root_field_id = $term_id)",
                            "ORDER BY status_order $dir, id DESC",
                        ];
                        break;
                    case 'id':
                        $this->add_raw_selects[] = 'cdef.title AS status_order';
                        $order_by                = [
                            "
                                JOIN custom_data_ticket AS cdata ON cdata.ticket_id = tickets.id AND cdata.root_field_id = $term_id
                                JOIN custom_def_ticket AS cdef ON cdef.id = cdata.field_id
                            ",
                            "ORDER BY status_order $dir, id DESC",
                        ];
                        break;

                }
                break;
        }

        return $order_by;
    }

    /**
     * Get the SQL parts we need in the query.
     *
     * @return array
     */
    public function getSqlParts()
    {
        if ($this->sql_parts !== null) {
            return $this->sql_parts;
        }

        $this->done_person_context_check = true;

        $tickets_table = 'tickets';

        $tr = App::getTranslator();

        $joins = [];

        $wheres_all = [];
        $wheres_any = [];

        // If we dont set a status, we will automatically
        // exclude 'hidden' tickets
        $set_status = false;

        foreach ([['all', $this->terms], ['any', $this->terms_any]] as $term_set) {
            if ($term_set[0] == 'all') {
                $wheres = &$wheres_all;
            } else {
                $wheres = &$wheres_any;
            }

            foreach ($term_set[1] as $info) {
                if (!$info || !is_array($info)) {
                    continue;
                }

                $join_id   = Util::requestUniqueId();
                $join_name = "j_$join_id";

                list($term_string, $op, $choice, $term, $term_id) = $info;

                // The term handlers below that only accept single values
                // will use $choice as a single value for brevity
                if (is_array($choice) and count($choice) == 1) {
                    $choice = Arrays::getFirstItem($choice);
                }

                if ($term_id) {
                    $this->getLogger()->logDebug(sprintf('Term: %s[%s] %s %s', $term, $term_id, $op, \DpSys\LowError\SystemErrorHandler::varToString($choice)));
                } else {
                    $this->getLogger()->logDebug(sprintf('Term: %s %s %s', $term, $op, \DpSys\LowError\SystemErrorHandler::varToString($choice)));
                }

                if (!$term) {
                    //skip empty terms
                    continue;
                }

                switch ($term) {
                    case self::TERM_ID:
                        $this->enableArchiveSearch();
                        $set_status = true;

                        $choice = is_array($choice) && isset($choice['ticket_id']) ? $choice['ticket_id'] : $choice;
                        if (!is_array($choice)) {
                            $choice = [$choice];
                        }

                        if (!$choice) {
                            $choice = [0];
                        }

                        if ($op == self::OP_IS) {
                            $wheres[] = "$tickets_table.id IN (".implode(',', $choice).')';
                        } else {
                            $wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);
                        }
                        break;
                    case self::TERM_RANGE_ID:
                        $this->enableArchiveSearch();
                        $set_status = true;

                        $choice = is_array($choice) && isset($choice['id']) ? $choice['id'] : $choice;
                        if (!$choice) {
                            $choice = 0;
                        }

                        $wheres[] = $this->_rangeMatch("$tickets_table.id", $op, $choice, true);

                        break;

                    case self::TERM_REF:
                        $this->enableArchiveSearch();
                        $set_status = true;

                        $wheres[] = $this->_stringMatch("$tickets_table.ref", $op, $choice, true);
                        break;

                    case self::TERM_PERSON_ID:
                        $choice = is_array($choice) && isset($choice['person_id']) ? $choice['person_id'] : $choice;
                        if (!is_array($choice)) {
                            $choice = [$choice];
                        }

                        if ($op == self::OP_IS) {
                            $wheres[] = "$tickets_table.person_id IN (".implode(',', $choice).')';
                        } else {
                            $wheres[] = $this->_rangeMatch("$tickets_table.person_id", $op, $choice, true);
                        }
                        break;

                    case self::TERM_ARCHIVE_SEARCH:
                        if ($choice) {
                            $this->enableArchiveSearch();
                        }
                        break;
                    case self::TERM_DEPARTMENT:

                        $this->affected_fields[] = 'ticket.department_id';

                        if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
                            $choice = (array) $choice;
                            foreach ($choice as $id) {
                                $choice = array_merge($choice, App::getDataService('Department')->getIdsInTree($id, true));
                            }
                            $choice = array_unique($choice, \SORT_NUMERIC);
                        }

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_DEPARTMENT;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.department_id", $op, $choice, true);
                        break;
                    case self::TERM_BRAND:
                        $this->affected_fields[] = 'ticket.brand_id';

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_BRAND;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.brand_id", $op, $choice, true);
                        break;

                    case self::TERM_EMAIL_ACCOUNT:
                        $this->enableArchiveSearch();
                        $this->affected_fields[] = 'ticket.email_account_id';

                        if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
                            $choice = (array) $choice;
                            $choice = array_unique($choice, \SORT_NUMERIC);
                        }

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_EMAIL_ACCOUNT;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.email_account_id", $op, $choice, true);
                        break;

                    case self::TERM_DELETED:
                        $this->affected_fields[] = 'ticket.status';
                        $this->affected_fields[] = 'ticket.hidden_status';

                        $set_status = true;
                        $wheres[]   = $this->_choiceMatch("$tickets_table.status", self::OP_IS, 'hidden');
                        $wheres[]   = $this->_choiceMatch("$tickets_table.hidden_status", self::OP_IS, 'deleted');

                        $this->enableArchiveSearch();

                        break;
                    case self::TERM_CATEGORY:
                        $this->affected_fields[] = 'ticket.category_id';

                        if (!$choice) {
                            $choice = '0';
                        }
                        if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
                            $choice = (array) $choice;
                        }

                        if ($choice) {
                            $childIds = App::getDb()->fetchAllCol('SELECT * FROM ticket_categories WHERE parent_id IN (:parent_id)', [
                                'parent_id' => implode(', ', $choice),
                            ]);

                            $choice = array_merge($choice, $childIds);
                            $choice = array_unique($choice, \SORT_NUMERIC);
                        }

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_CATEGORY;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.category_id", $op, $choice, true);
                        break;
                    case self::TERM_PRODUCT:
                        $this->affected_fields[] = 'ticket.product_id';

                        if (!$choice) {
                            $choice = '0';
                        }
                        if ($choice && (!is_array($choice) || !in_array('0', $choice))) {
                            $choice = (array) $choice;
                        }

                        if ($choice) {
                            $childIds = App::getDb()->fetchAllCol('SELECT * FROM products WHERE parent_id IN (:parent_id)', [
                                'parent_id' => implode(', ', $choice),
                            ]);

                            $choice = array_merge($choice, $childIds);
                            $choice = array_unique($choice, \SORT_NUMERIC);
                        }

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_PRODUCT;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.product_id", $op, $choice, true);
                        break;
                    case self::TERM_PRIORITY:
                        $this->affected_fields[] = 'ticket.priority_id';

                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_PRIORITY;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.priority_id", $op, $choice, true);
                        break;
                    case self::TERM_URGENCY:
                        $this->affected_fields[] = 'ticket.urgency';
                        $wheres[]                = $this->_rangeMatch("$tickets_table.urgency", $op, $choice);
                        break;
                    case self::TERM_DATE_CREATED:
                        $wheres[] = $this->_dateMatch("$tickets_table.date_created", $op, $choice);
                        break;
                    case self::TERM_DATE_STATUS:
                        $wheres[] = $this->_dateMatch("$tickets_table.date_status", $op, $choice);
                        break;
                    case self::TERM_DATE_RESOLVED:
                        $this->affected_fields[] = 'ticket.date_resolved';
                        $wheres[]                = $this->_dateMatch("$tickets_table.date_resolved", $op, $choice);
                        $wheres[]                = $this->_choiceMatch("$tickets_table.status", 'is', ['resolved']);
                        break;
                    case self::TERM_DATE_ON_HOLD:
                        $this->affected_fields[] = 'ticket.date_on_hold';
                        $wheres[]                = $this->_dateMatch("$tickets_table.date_on_hold", $op, $choice);
                        $wheres[]                = $this->_choiceMatch("$tickets_table.status", 'is', ['awaiting_agent']);
                        break;
                    case self::TERM_DATE_ARCHIVED:
                        $this->affected_fields[] = 'ticket.date_archived';
                        if (!$this->is_testing) {
                            $this->summary[] = $this->_dateRangeSummary($tr->phrase('agent.general.date_archived'), $op, $choice);
                        }
                        $wheres[] = $this->_dateMatch("$tickets_table.date_archived", $op, $choice);
                        $wheres[] = $this->_choiceMatch("$tickets_table.status", 'is', ['archived']);
                        break;
                    case self::TERM_DATE_LAST_USER_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_user_reply';

                        $wheres[] = $this->_dateMatch("$tickets_table.date_last_user_reply", $op, $choice);
                        break;
                    case self::TERM_DATE_LAST_AGENT_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_agent_reply';

                        $wheres[] = $this->_dateMatch("$tickets_table.date_last_agent_reply", $op, $choice);
                        break;
                    case self::TERM_DATE_LAST_REPLY:
                        $this->affected_fields[] = 'ticket.date_last_reply';

                        $wheres[] = $this->_dateMatch("GREATEST(COALESCE($tickets_table.date_last_agent_reply, '0000-00-00'), COALESCE($tickets_table.date_last_user_reply, '0000-00-00'), $tickets_table.date_created)", $op, $choice);
                        break;
                    case self::TERM_WORKFLOW:
                        $this->affected_fields[] = 'ticket.workflow_id';
                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_WORKFLOW;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.workflow_id", $op, $choice, true);
                        break;
                    case self::TERM_FEEDBACK_RATING:

                        $choice = (is_array($choice) && isset($choice['rating'])) ? $choice['rating'] : 'set';

                        if ($choice == 'set') {
                            if ($op == self::OP_IS) {
                                $wheres[] = "$tickets_table.feedback_rating IS NOT NULL";
                            } else {
                                $wheres[] = "$tickets_table.feedback_rating IS NULL";
                            }
                        } else {
                            $op = $op == self::OP_IS ? '=' : '!=';

                            $check = '';
                            if ($choice == 'positive') {
                                $check .= "$tickets_table.feedback_rating $op 1";
                            } elseif ($choice == 'negative') {
                                $check .= "$tickets_table.feedback_rating $op -1";
                            } else {
                                $check .= "$tickets_table.feedback_rating $op 0";
                            }

                            if ($op == '=') {
                                $wheres[] = "($tickets_table.feedback_rating IS NOT NULL AND $check)";
                            } else {
                                $wheres[] = "($tickets_table.feedback_rating IS NULL OR $check)";
                            }
                        }

                        break;
                    case self::TERM_FEEDBACK_LINKS:

                        $joins[] = [
                            'ticket_feedback_links',
                            "LEFT JOIN ticket_feedback_links AS $join_name ON ($join_name.ticket_id = tickets.id)",
                        ];

                        switch ($op) {
                            case 'not_isset':
                                $wheres[] = "$join_name.id IS NULL";
                                break;
                            case 'isset':
                                $wheres[] = "$join_name.id IS NOT NULL";
                                break;
                            case self::OP_IS:
                                if (!is_array($choice)) {
                                    $choice = explode(',', $choice);
                                }
                                $wheres[] = $this->_choiceMatch("$join_name.feedback_id", $op, $choice, true);
                                break;
                        }

                        break;

                    case self::TERM_SLA:

                        $choice = (array) ((is_array($choice) && isset($choice['sla_id'])) ? $choice['sla_id'] : $choice);
                        if (!$choice) {
                            ((is_array($choice) && isset($choice['sla_ids'])) ? $choice['sla_ids'] : $choice);
                        }
                        if (!$choice) {
                            break;
                        }

                        $this->affected_fields[] = 'ticket.sla_id';

                        $choices_in = [];
                        foreach ($choice as $c) {
                            $choices_in[] = $this->quoteDbValue($c);
                        }
                        $choices_in = implode(',', $choices_in);

                        switch ($op) {
                            case self::OP_IS:
                            case self::OP_CONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)",
                                ];
                                $wheres[] = "$join_name.sla_id IN ($choices_in)";
                                break;

                            case self::OP_NOT:
                            case self::OP_NOTCONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.sla_id IN ($choices_in))",
                                ];
                                $wheres[] = "$join_name.ticket_id IS NULL";
                                break;
                        }
                        break;

                    case self::TERM_SLA_STATUS:

                        if (is_array($choice) && isset($choice['sla_status'])) {
                            $statuses = (array) $choice['sla_status'];
                            $sla_ids  = (array) (isset($choice['sla_id']) ? $choice['sla_id'] : []);
                            if (!$sla_ids) {
                                (isset($choice['sla_ids']) ? $choice['sla_ids'] : []);
                            }
                        } else {
                            $statuses = (array) $choice;
                            $sla_ids  = [];
                        }
                        if (!$statuses && !$sla_ids) {
                            break;
                        }

                        if (!$statuses) {
                            $statuses       = ['ok', 'warning', 'fail'];
                            $status_summary = false;
                        } else {
                            $status_summary = true;
                        }

                        $this->affected_fields[] = 'ticket.sla_status';
                        if ($sla_ids) {
                            $this->affected_fields[] = 'ticket.sla_id';
                        }

                        $statuses_in = [];
                        $sla_ids_in  = [];

                        foreach ($statuses as $c) {
                            $statuses_in[] = $this->quoteDbValue($c);
                        }
                        foreach ($sla_ids as $c) {
                            if ($c) {
                                $sla_ids_in[] = $this->quoteDbValue($c);
                            }
                        }
                        $statuses_in = implode(',', $statuses_in);
                        $sla_ids_in  = implode(',', $sla_ids_in);

                        switch ($op) {
                            case self::OP_IS:
                            case self::OP_CONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)",
                                ];
                                $wheres[] = "$join_name.sla_status IN ($statuses_in)"
                                    .($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '');
                                break;

                            case self::OP_NOT:
                            case self::OP_NOTCONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id"
                                    ." AND $join_name.sla_status IN ($statuses_in)"
                                    .($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '').')',
                                ];
                                $wheres[] = "$join_name.ticket_id IS NULL";
                                break;
                        }
                        break;

                    case self::TERM_SLA_COMPLETED:

                        if (is_array($choice) && isset($choice['is_completed'])) {
                            $completed = (array) $choice['is_completed'];
                            $sla_ids   = (array) (isset($choice['sla_id']) ? $choice['sla_id'] : []);
                        } else {
                            $completed = (array) $choice;
                            $sla_ids   = [];
                        }
                        if (!$completed && !$sla_ids) {
                            break;
                        }

                        if (!$completed) {
                            $completed      = [1, 0];
                            $status_summary = false;
                        } else {
                            $status_summary = true;
                        }

                        $this->affected_fields[] = 'ticket.sla_completed';
                        if ($sla_ids) {
                            $this->affected_fields[] = 'ticket.sla_id';
                        }

                        $completed_in = [];
                        $sla_ids_in   = [];

                        foreach ($completed as $c) {
                            $completed_in[] = $this->quoteDbValue($c);
                        }
                        foreach ($sla_ids as $c) {
                            if ($c) {
                                $sla_ids_in[] = $this->quoteDbValue($c);
                            }
                        }
                        $completed_in = implode(',', $completed_in);
                        $sla_ids_in   = implode(',', $sla_ids_in);

                        switch ($op) {
                            case self::OP_IS:
                            case self::OP_CONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id)",
                                ];
                                $wheres[] = "$join_name.is_completed IN ($completed_in)"
                                    .($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '');
                                break;

                            case self::OP_NOT:
                            case self::OP_NOTCONTAINS:
                                $joins[] = [
                                    'ticket_slas',
                                    "LEFT JOIN ticket_slas AS $join_name ON ($join_name.ticket_id = tickets.id"
                                    ."AND $join_name.is_completed IN ($completed_in)"
                                    .($sla_ids_in ? " AND $join_name.sla_id IN ($sla_ids_in)" : '').')',
                                ];
                                $wheres[] = "$join_name.ticket_id IS NULL";
                                break;
                        }
                        break;

                    case self::TERM_LANGUAGE:
                        $this->affected_fields[] = 'ticket.language_id';
                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_LANGUAGE;
                        }

                        if (is_array($choice)) {
                            $choice = array_pop($choice);
                        }

                        if ($choice == App::getSetting('core.default_language_id')) {
                            $wheres[] = '('.$this->_choiceMatch("$tickets_table.language_id", $op, $choice, true).' OR '.$this->_choiceMatch("$tickets_table.language_id", $op, 0, true).')';
                        } else {
                            $wheres[] = $this->_choiceMatch("$tickets_table.language_id", $op, $choice, true);
                        }
                        break;
                    case self::TERM_AGENT:
                        $this->affected_fields[] = 'ticket.agent_id';

                        $info       = $this->_normalizeAgentChoice($choice);
                        $unassigned = $info['unassigned'];
                        $agent_ids  = $info['agent_ids'];
                        $not_id     = $info['not_id'];

                        if ($not_id) {
                            $wheres[] = "$tickets_table.agent_id != ".$not_id;
                        } else {
                            $w = [];

                            if ($unassigned) {
                                if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
                                    $w[] = "$tickets_table.agent_id IS NULL";
                                } else {
                                    $w[] = "$tickets_table.agent_id IS NOT NULL";
                                }
                            }

                            if ($agent_ids) {
                                if (count($agent_ids) == 1) {
                                    $this->specific_fields[] = self::TERM_AGENT;
                                }

                                $w[] = $this->_choiceMatch("$tickets_table.agent_id", $op, $agent_ids, true);
                            }

                            if (count($w) === 1) {
                                $wheres[] = $w[0];
                            } elseif ($w) {
                                $wheres[] = '(('.implode(') OR (', $w).'))';
                            }
                        }
                        break;
                    case self::TERM_AGENT_TEAM:
                        $this->affected_fields[] = 'ticket.agent_team_id';

                        $info     = $this->_normalizeAgentTeamChoice($choice);
                        $team_ids = $info['team_ids'];
                        $not_ids  = $info['not_ids'];
                        $no_team  = $info['no_team'];

                        if ($not_ids) {
                            $wheres[] = $this->_choiceMatch("$tickets_table.agent_team_id", 'not', $team_ids, true);
                        } else {
                            $w = [];

                            if ($no_team) {
                                if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
                                    $w[] = "$tickets_table.agent_team_id IS NULL";
                                } else {
                                    $w[] = "$tickets_table.agent_team_id IS NOT NULL";
                                }
                            }

                            if ($team_ids) {
                                if (count($choice) == 1) {
                                    $this->specific_fields[] = self::TERM_AGENT_TEAM;
                                }

                                $w[] = $this->_choiceMatch("$tickets_table.agent_team_id", $op, $team_ids, true);
                            }

                            if (count($w) === 1) {
                                $wheres[] = $w[0];
                            } elseif ($w) {
                                $wheres[] = '(('.implode(') OR (', $w).'))';
                            }
                        }
                        break;
                    case self::TERM_STATUS:

                        $this->affected_fields[] = 'ticket.status';
                        $set_status              = true;

                        $show_status   = [];
                        $hidden_status = [];

                        $choice_str = [];

                        foreach ((array) $choice as $c) {
                            if (strpos($c, '.') !== false) {
                                list($status, $hstatus) = explode('.', $c, 2);
                                $hidden_status[]        = $hstatus;
                                if ($status == 'hidden' && $tr->hasPhrase('agent.tickets.hidden_status_'.$hstatus)) {
                                    $choice_str[] = $tr->phrase('agent.tickets.hidden_status_'.$hstatus);
                                }
                                $this->enableArchiveSearch();
                            } else {
                                $show_status[] = $show_status;
                                $choice_str[]  = $tr->phrase('agent.tickets.status_'.$c);
                                if ($c != 'awaiting_agent' && $c != 'awaiting_user' && $c != 'resolved') {
                                    $this->enableArchiveSearch();
                                }
                            }
                        }

                        $choice_str = implode(' or ', $choice_str);

                        $w = '(';
                        if ($show_status) {
                            $w .= '(';
                            $w .= $this->_choiceMatch("$tickets_table.status", $op, $choice);
                            $w .= ')';
                        } else {
                            $w .= '(';
                            $w .= "$tickets_table.status = 'hidden' AND ";
                            $w .= $this->_choiceMatch("$tickets_table.hidden_status", $op, $hidden_status);
                            $this->enableArchiveSearch();
                            $w .= ')';
                        }
                        $w .= ')';

                        $wheres[] = $w;
                        break;
                    case self::TERM_HIDDEN_STATUS:
                        $this->affected_fields[] = 'ticket.hidden_status';

                        $choice_str = [];
                        foreach ((array) $choice as $c) {
                            $choice_str[] = $tr->phrase('agent.tickets.hidden_status_'.$c);
                        }
                        $choice_str = implode(', ', $choice_str);

                        $wheres[] = $this->_choiceMatch("$tickets_table.hidden_status", $op, $choice);
                        $this->enableArchiveSearch();

                        break;
                    case self::TERM_HOLD:

                        $this->affected_fields[] = 'ticket.is_hold';

                        // Op is irrelevant. or, it's always "is", and choice is yes/no

                        if ($choice) {
                            $wheres[] = 'tickets.is_hold = 1';
                        } else {
                            $wheres[] = 'tickets.is_hold = 0';
                        }

                        break;
                    case self::TERM_ORGANIZATION:
                        if (isset($choice['organization_ids']) && is_array($choice['organization_ids'])) {
                            $choice = $choice['organization_ids'];
                        }
                        if (!is_array($choice)) {
                            $choice = explode(',', $choice);
                        }
                        $choice = Arrays::removeNull($choice);
                        $choice = Arrays::removeEmptyString($choice);
                        if (!empty($choice)) {
                            if (count($choice) == 1) {
                                $this->specific_fields[] = self::TERM_ORGANIZATION;
                            }

                            $wheres[] = $this->_choiceMatch("$tickets_table.organization_id", $op, $choice, true);
                        }
                        break;
                    case self::TERM_PARTICIPANT:
                        $this->affected_fields[] = 'ticket.participants';
                        $joins[]                 = [
                            'tickets_participants',
                            "LEFT JOIN tickets_participants AS $join_name ON $join_name.ticket_id = tickets.id ",
                        ];
                        $field = $join_name.'.person_id';

                        $choice_info = $this->_normalizeAgentChoice($choice);
                        if (!empty($choice_info['agent_ids'])) {
                            $choice = $choice_info['agent_ids'];
                        } else {
                            continue;
                        }

                        $wheres[] = $this->_choiceMatch($field, $op, $choice);
                        break;
                    case self::TERM_PERSON:
                        if (count($choice) == 1) {
                            $this->specific_fields[] = self::TERM_PERSON;
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.person_id", $op, $choice, true);
                        break;

                    case self::TERM_IP_ADDRESS:
                        $choice = is_array($choice) ? array_pop($choice) : $choice;
                        $choice = preg_replace('#[^0-9\.]#', '', $choice);

                        $joins[] = [
                            'tickets_messages',
                            "LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.ip_address != '')",
                        ];
                        $field = "$join_name.ip_address";

                        // If last char is a dot, then do a wildcard suffix search
                        if (substr($choice, -1, 1) == '.') {
                            $wheres[] = $this->_stringMatch($field, $op, $choice, true, true);
                        } else {
                            $wheres[] = $this->_stringMatch($field, $op, $choice);
                        }

                        break;

                    case self::TERM_SUBJECT:
                        $this->affected_fields[] = 'ticket.subject';
                        $field                   = 'tickets.subject';

                        $wheres[] = $this->_stringMatch($field, $op, $choice);
                        break;

                    case self::TERM_SUBJECT_ADV:
                        $this->affected_fields[] = 'ticket.subject';
                        $field                   = 'tickets.subject';
                        $string                  = $choice['query'];
                        $type                    = !empty($choice['type']) ? $choice['type'] : 'phrase';

                        $wheres[] = $this->_stringSearch($field, $op, $string, $type);
                        break;

                    case self::TERM_MESSAGE:
                        $this->affected_fields[] = 'ticket.message';

                        $string = $choice;

                        if (is_array($string)) {
                            $string = array_pop($string);
                        }

                        $type = 'and';

                        $this->affected_fields[] = 'ticket.message';
                        $joins[]                 = [
                            'tickets_messages',
                            "LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id)",
                        ];
                        $field    = "$join_name.message";
                        $wheres[] = $this->_stringSearch($field, $op, $string, $type);

                        break;

                    case self::TERM_MESSAGE_ADV:

                        $string = $choice['query'];
                        $type   = !empty($choice['type']) ? $choice['type'] : 'phrase';

                        $this->affected_fields[] = 'ticket.message';
                        $joins[]                 = [
                            'tickets_messages',
                            "LEFT JOIN tickets_messages AS $join_name ON ($join_name.ticket_id = tickets.id)",
                        ];
                        $field = "$join_name.message";

                        $w   = [];
                        $w[] = '('.$this->_stringSearch($field, $op, $string, $type).')';

                        if (!empty($choice['who'])) {
                            $join_name2 = $join_name.'_u';
                            $joins[]    = [
                                'people',
                                "LEFT JOIN people AS $join_name2 ON ($join_name2.id = $join_name.person_id)",
                            ];

                            if ($choice['who'] == 'agent') {
                                $w[] = "($join_name2.is_agent = 1)";
                            } else {
                                $w[] = "($join_name2.is_agent = 0)";
                            }
                        }

                        if (!empty($choice['date_op']) && $choice['date_op']) {
                            $w[] = '('.$this->_dateMatch("$join_name.date_created", $choice['date_op'], $choice['date']).')';
                        }

                        $wheres[] = '('.implode(' AND ', $w).')';
                        break;

                    case self::TERM_FLAGGED:

                        $this->affected_fields[] = 'tickets_flagged';
                        $joins[]                 = [
                            'tickets_flagged',
                            "LEFT JOIN tickets_flagged AS $join_name ON $join_name.ticket_id = tickets.id ",
                        ];

                        ++$this->used_person_context;

                        $color = $choice;
                        if ($color == 'any') {
                            $wheres[] = $join_name.'.person_id = '.$this->person->id;
                        } else {
                            $wheres[] = '('.$join_name.'.person_id = '.$this->person->id.' AND '.$this->_stringMatch($join_name.'.color', $op, $color).')';
                        }

                        break;

                    case self::TERM_LABEL:
                        $this->affected_fields[] = 'ticket.labels';
                        $this->_normalizeOpAndChoice($op, $choice);

                        $choices_in = [];
                        if (is_array($choice)) {
                            foreach ((array) $choice as $c) {
                                $choices_in[] = $this->quoteDbValue($c);
                            }
                            $choices_in = implode(',', $choices_in);
                            if (!$choices_in) {
                                $choices_in = '\'\'';
                            }
                        }

                        switch ($op) {
                            case self::OP_IS:
                                $joins[] = [
                                    'labels_tickets',
                                    "LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id)",
                                ];
                                $wheres[] = "$join_name.label = ".$this->quoteDbValue($choice);
                                break;
                            case self::OP_NOT:
                                $joins[] = [
                                    'labels_tickets',
                                    "LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label = ".$this->quoteDbValue($choice).')',
                                ];
                                $wheres[] = "$join_name.ticket_id IS NULL";
                                break;
                            case self::OP_CONTAINS:
                                $joins[] = [
                                    'labels_tickets',
                                    "LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id)",
                                ];
                                $wheres[] = "$join_name.label IN ($choices_in)";
                                break;

                            case self::OP_NOTCONTAINS:
                                $joins[] = [
                                    'labels_tickets',
                                    "LEFT JOIN labels_tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.label IN ($choices_in))",
                                ];
                                $wheres[] = "$join_name.ticket_id IS NULL";
                                break;
                        }
                        break;

                    case self::TERM_TICKET_FIELD:
                        $fieldDef = App::getEntityRepository(Entity\CustomDefTicket::class)->find($term_id);
                        if (!$fieldDef) {
                            break;
                        }

                        $field = $fieldDef;

                        $this->affected_fields[] = 'ticket.custom_data_ticket_'.$fieldDef['id'];

                        $search_type = $fieldDef->getHandler()->getSearchType();

                        $isDate = isset($choice['date1']) || isset($choice['date1_relative']);
                        if (is_array($choice) && isset($choice['value']) && !$isDate) {
                            $choice = $choice['value'];
                        }
                        if (is_array($choice) && isset($choice['custom_fields'])) {
                            $choice = $choice['custom_fields'];
                        }
                        if (is_array($choice) && isset($choice['field_'.$field->getId()])) {
                            $choice = $choice['field_'.$field->getId()];
                        }
                        if ($fieldDef->isCurrencyType()) {
                            $currencyId = $fieldDef->getOption('currency_id');
                            if (!$currencyId) {
                                break;
                            }

                            $currency = App::getEntityRepository(Currency::class)->find($currencyId);
                            if (!$currency) {
                                break;
                            }

                            $choice *= $currency->getDelimiter();
                        }

                        switch ($search_type) {
                            case 'input':
                            case 'value':

                                if (is_array($choice) && !$isDate) {
                                    $choice = array_pop($choice);
                                }

                                if ($choice === null) {
                                    $choice = 'DP_NO_SELECTION';
                                }

                                $joins[] = [
                                    'custom_data_ticket',
                                    "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.field_id = $term_id)",
                                ];

                                $field = 'custom_data_ticket_'.$join_id.'.'.$search_type;
                                switch ($op) {
                                    case self::OP_IS:
                                        if ($choice == 'DP_NO_SELECTION') {
                                            $wheres[] = "$field IS NULL";
                                        } else {
                                            $wheres[] = "$field = ".$this->quoteDbValue($choice);
                                        }
                                        break;
                                    case self::OP_NOT:
                                        if ($choice == 'DP_NO_SELECTION') {
                                            $wheres[] = "$field IS NOT NULL";
                                        } else {
                                            $w = "$field != ".$this->quoteDbValue($choice);

                                            if ($choice != '') {
                                                $w = "($w OR $field IS NULL)";
                                            }

                                            $wheres[] = $w;
                                        }
                                        break;
                                    case self::OP_CONTAINS:
                                    case self::OP_NOTCONTAINS:
                                        if ($op === self::OP_NOTCONTAINS) {
                                            $op = 'NOT LIKE';
                                        } else {
                                            $op = 'LIKE';
                                        }
                                        $w = "$field $op ".$this->quoteDbValue('%'.$choice.'%');

                                        if ($op == self::OP_NOTCONTAINS) {
                                            $w = "($w OR $field IS NULL)";
                                        }

                                        $wheres[] = $w;

                                        break;
                                    case self::OP_LTE:
                                    case self::OP_GTE:
                                        $op = self::OP_LTE === $op ? '<=' : '>=';
                                        if ($isDate) {
                                            if (!empty($choice['date1'])) {
                                                $wheres[] = "$field $op ".(int) $choice['date1'];
                                            } elseif (!empty($choice['date1_relative'])) {
                                                $wheres[] = "$field $op ".strtotime('-'.$choice['date1_relative'].' '.$choice['date1_relative_type']);
                                            }
                                        } elseif ($fieldDef->isCurrencyType()) {
                                            $wheres[] = "$field $op ".$this->quoteDbValue($choice);
                                        } elseif (!is_array($choice) && strlen($choice) && 'DP_NO_SELECTION' !== $choice) {
                                            $wheres[] = "$field $op ".$this->quoteDbValue('%'.$choice.'%');
                                        }
                                        break;
                                    case self::OP_BETWEEN:
                                        if ($isDate) {
                                            if (!empty($choice['date1'])) {
                                                $wheres[] = $field.' BETWEEN '.(int) $choice['date1'].' AND '.(int) @$choice['date2'];
                                            } elseif (!empty($choice['date1_relative'])) {
                                                $d1 = strtotime('-'.$choice['date1_relative'].' '.$choice['date1_relative_type']);
                                                $d2 = strtotime('-'.@$choice['date2_relative'].' '.@$choice['date2_relative_type']);
                                                if ($d1 < $d2) {
                                                    $wheres[] = "$field BETWEEN $d1 AND $d2";
                                                } else {
                                                    $wheres[] = "$field BETWEEN $d2 AND $d1";
                                                }
                                            }
                                        }
                                        break;
                                    case self::OP_NOT_ISSET:
                                        $wheres[] = "$field IS NULL";
                                        break;
                                    case self::OP_ISSET:
                                        $wheres[] = "$field IS NOT NULL";
                                        break;
                                    case self::OP_EMPTY:
                                        $wheres[] = "$field IS NULL OR $field = ''";
                                        break;
                                    case self::OP_NOT_EMPTY:
                                        $wheres[] = "$field != ''";
                                        break;
                                }

                                break;

                            case 'id':
                                $join_id    = Util::requestUniqueId();
                                $choices_in = [];

                                if (is_array($choice) && count($choice) === 1 && array_key_exists(0, $choice)) {
                                    $choice = $choice[0];
                                }

                                if ($choice == '-1') {
                                    $choice = 'DP_NO_SELECTION';
                                }

                                if ($choice != 'DP_NO_SELECTION') {
                                    $choice = (array) $choice;
                                    if (isset($choice["field_{$fieldDef->getId()}"])) {
                                        $choice = $choice["field_{$fieldDef->getId()}"];
                                    }
                                    if (!is_array($choice)) {
                                        $choice = [$choice];
                                    }

                                    /* @var  $children */
                                    $children_titles = array_map(function ($v) {
                                        return trim(strtolower($v));
                                    }, $fieldDef->getAllChildTitles());

                                    foreach ($choice as $c) {
                                        if (!is_scalar($c)) {
                                            continue;
                                        }
                                        // if its an invalid id, try to find it based off a title match
                                        if ((!is_int($c) && !ctype_digit($c)) || !array_key_exists($c, $children_titles)) {
                                            $c = array_search(trim(strtolower($c)), $children_titles);
                                        }
                                        $choices_in[] = (int) $c;
                                    }
                                    $choices_in = implode(',', $choices_in);

                                    if (!$choice && !$choices_in) {
                                        $choice = 'DP_NO_SELECTION';
                                    } elseif (!$choices_in) {
                                        $choice     = [0];
                                        $choices_in = '0';
                                    }
                                }

                                // collect all sub-choices
                                $choices_in = !is_array($choices_in) ? explode(',', $choices_in) : $choices_in;
                                $iterator   = function ($parentId) use ($field, &$choices_in, &$iterator) {
                                    /** @var Entity\CustomDefAbstract $child */
                                    foreach ($field->getChildren() as $child) {
                                        if ($parentId && (int) $child->getOption('parent_id') === (int) $parentId) {
                                            $choices_in[] = $child->getId();
                                            $iterator($child->getId());
                                        }
                                    }
                                };

                                foreach ($choices_in as $choiceId) {
                                    if ($choiceId) {
                                        $iterator($choiceId);
                                    }
                                }

                                $choices_in = implode(',', $choices_in);

                                $field = 'custom_data_ticket_'.$join_id.'.field_id';
                                switch ($op) {
                                    case self::OP_CONTAINS:
                                    case self::OP_IS:
                                        if ($choice == 'DP_NO_SELECTION') {
                                            $joins[] = [
                                                'custom_data_ticket',
                                                "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$fieldDef->id})",
                                            ];
                                            $wheres[] = "custom_data_ticket_$join_id.id IS NULL";
                                        } else {
                                            $joins[] = [
                                                'custom_data_ticket',
                                                "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND $field IN ($choices_in))",
                                            ];
                                            $wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
                                        }
                                        break;

                                    case self::OP_NOTCONTAINS:
                                    case self::OP_NOT:
                                        if ($choice == 'DP_NO_SELECTION') {
                                            $joins[] = [
                                                'custom_data_ticket',
                                                "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$fieldDef->id})",
                                            ];
                                            $wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
                                        } else {
                                            $joins[] = [
                                                'custom_data_ticket',
                                                "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND $field IN ($choices_in))",
                                            ];
                                            $wheres[] = "custom_data_ticket_$join_id.id IS NULL";
                                        }
                                        break;
                                    case self::OP_NOT_ISSET:
                                        $joins[] = [
                                            'custom_data_ticket',
                                            "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$fieldDef->id})",
                                        ];
                                        $wheres[] = "custom_data_ticket_$join_id.id IS NULL";
                                        break;
                                    case self::OP_ISSET:
                                        $joins[] = [
                                            'custom_data_ticket',
                                            "LEFT JOIN custom_data_ticket AS custom_data_ticket_$join_id ON (custom_data_ticket_$join_id.ticket_id = tickets.id AND custom_data_ticket_$join_id.root_field_id = {$fieldDef->id})",
                                        ];
                                        $wheres[] = "custom_data_ticket_$join_id.id IS NOT NULL";
                                        break;
                                }
                                break;
                        }
                        break; // end break TERM_TICKET_FIELD

                    case 'time_waiting':
                    case self::TERM_USER_WAITING:
                        $this->affected_fields[] = 'ticket.date_user_waiting';

                        $choice = $this->normalizeWaitingTime($choice);

                        if (is_array($choice) && isset($choice['waiting_time'])) {
                            $choice = new \DateTime('-'.\Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']).' seconds');

                            // Waiting time is inversed when supplied in relative format like this.
                            // If we want to know 'waiting time is gte 24 hours', then the date from normaliseWaitingTime is the upper limit of what we want.
                            // 'waiting time is gte 24 hours' == 'date_user_waiting lte 2012-01-02'
                            $op = $this->invertOp($op);
                        }

                        if ($choice) {
                            $wheres[] = $this->_dateMatch('tickets.date_user_waiting', $op, $choice);
                        }
                        break;

                    case self::TERM_AGENT_WAITING:
                        $this->affected_fields[] = 'ticket.date_agent_waiting';

                        $choice = $this->normalizeWaitingTime($choice);

                        if (is_array($choice) && isset($choice['waiting_time'])) {
                            $choice = new \DateTime('-'.\Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']).' seconds');
                            $op     = $this->invertOp($op);
                        }

                        if ($choice) {
                            $wheres[] = $this->_dateMatch("$tickets_table.date_agent_waiting", $op, $choice);
                        }
                        break;

                    case self::TERM_TOTAL_USER_WAITING:
                        $this->affected_fields[] = 'ticket.total_user_waiting';
                        $now                     = time();

                        // Need the check on waiting_time because it could be date1/date2 instead
                        if (is_array($choice) && isset($choice['waiting_time'])) {
                            $choice = \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']);
                        }

                        $wheres[] = $this->_rangeMatch("(tickets.total_user_waiting + ($now - COALESCE(UNIX_TIMESTAMP(date_user_waiting))))", $op, $choice);
                        break;

                    case self::TERM_CREATION_SYSTEM:
                        $set_status = true;
                        break;

                    case 'escalation_eliminator':
                        /* @var $trigger \Application\DeskPRO\Entity\TicketEscalation */
                        if ($choice instanceof Entity\TicketEscalation) {
                            $escalation = $choice;
                        } else {
                            $escalation = $choice['escalation'];
                        }
                        $field = $escalation->getTicketTimeField();
                        if (!$field) {
                            break;
                        }

                        $joins[] = [
                            'ticket_escalation_logs',
                            "LEFT JOIN ticket_escalation_logs AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.escalation_id = {$escalation->id} AND $join_name.date_criteria = tickets.$field)",
                        ];

                        $wheres[] = "$join_name.id IS NULL";
                        break;

                    case 'time_created':
                    case 'time_last_user_reply':
                        switch ($op) {
                            case 'before':
                                $operator = '<=';
                                break;
                            case 'after':
                                $operator = '>=';
                                break;
                            default:
                                $operator = '=';
                        }

                        foreach ($choice as $k => $v) {
                            $choice[$k] = preg_replace('[^0-9]', '', $choice[$k]);
                        }

                        $column   = str_replace('time', 'date', $term);
                        $wheres[] = "$column IS NOT NULL AND TIME($column) $operator '{$choice['hour1']}:{$choice['minute1']}:00'";
                        break;

                    case self::TERM_DAY_CREATED:
                        $days = (is_array($choice) && isset($choice['days'])) ? $choice['days'] : $choice;
                        if (!$days || !is_array($days)) {
                            continue;
                        }
                        $wheres[] = $this->_choiceMatch("DATE_FORMAT(tickets.date_created, '%w')", $op, $days, true);
                        break;

                    case self::TERM_PROBLEMS:

                        if (!$choice) {
                            break;
                        }

                        $this->affected_fields[] = 'ticket.problems';

                        $choices_in = [];
                        foreach ($choice as $c) {
                            $choices_in[] = (int) $c;
                        }
                        $choices_in = implode(',', $choices_in);

                        switch ($op) {
                            case self::OP_IS:
                            case self::OP_CONTAINS:
                                $joins[] = [
                                    'problem2tickets',
                                    "JOIN problem2tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.problem_id IN ($choices_in))",
                                ];
                                break;

                            case self::OP_NOT:
                            case self::OP_NOTCONTAINS:
                                $joins[] = [
                                    'problem2tickets',
                                    "JOIN problem2tickets AS $join_name ON ($join_name.ticket_id = tickets.id AND $join_name.problem_id NOT IN ($choices_in))",
                                ];
                                break;
                        }
                        break;
                    case 'brand_id':
                        $this->affected_fields[] = 'ticket.brand_id';

                        if (count($choice) == 1) {
                            $this->specific_fields[] = 'brand';
                        }

                        $wheres[] = $this->_choiceMatch("$tickets_table.brand_id", $op, $choice, true);
                        break;
                    default:
                        $e = new \InvalidArgumentException("Unknown term: $term");
                        \DpSys\LowError\SystemErrorHandler::logErrorInfo(\DpSys\LowError\SystemErrorHandler::getExceptionInfo($e));
                        break;
                }
            }
        }

        if (!$set_status) {
            $wheres_all[] = $this->_choiceMatch("$tickets_table.status", self::OP_NOT, 'hidden');
        }

        $this->sql_parts = [
            'joins'      => $joins,
            'wheres'     => $wheres_all,
            'wheres_any' => $wheres_any,
        ];

        return $this->sql_parts;
    }

    /**
     * Check a specific ticket against these terms to see if it matches.
     *
     * @param Ticket $ticket
     * @param null   $context
     * @param null   $failed_term
     * @param array  $use_term_cache
     *
     * @return bool
     */
    public function doesTicketMatch(Entity\Ticket $ticket, $context = null, &$failed_term = null, array &$use_term_cache = null)
    {
        if ($this->is_filter_search && $ticket->getHiddenStatus()) {
            return false;
        }

        $ignore_terms = [];
        if ($use_term_cache === null) {
            $use_term_cache = [];
        }

        foreach ($this->terms as $idx => $info) {
            list($term, $op, $choice) = $info;

            if ($op == 'ignore' || isset($ignore_terms[$term])) {
                $ignore_terms[$term] = 1;
                continue;
            }

            $failed_term = $term;

            if (isset($use_term_cache[$idx])) {
                $result = $use_term_cache[$idx];
            } else {
                $o      = $this->used_person_context;
                $result = $this->doesTicketMatchTerm($ticket, $context, $term, $op, $choice);

                // if that term didnt use context, cache result
                if ($o === $this->used_person_context) {
                    $use_term_cache[$idx] = $result;
                }
            }

            if (!$result) {
                return false;
            }
        }

        if ($this->person_search) {
            if (isset($use_term_cache['person_search'])) {
                $result = $use_term_cache['person_search'];
            } else {
                $result = $this->person_search->doesPersontMatch($ticket->getPerson(), $ticket);
                if (!$this->person_search->needsPersonContext()) {
                    $use_term_cache['person_search'] = $result;
                }
            }
            if (!$result) {
                return false;
            }
        }

        $failed_term = null;

        return true;
    }

    private function doesTicketMatchTerm(Entity\Ticket $ticket, $context, $term, $op, $choice)
    {
        $this->done_person_context_check = true;

        switch ($term) {
            case self::TERM_STATUS:
                if (!$this->_testChoiceMatch($ticket->getStatusCode(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_DEPARTMENT:
                if (count($choice) == 1) {
                    $choice = Arrays::getFirstItem($choice);
                }
                if (!is_array($choice)) {
                    $choice = [$choice];
                }

                foreach ($choice as $id) {
                    $choice = array_merge($choice, App::getDataService('Department')->getIdsInTree($id, true));
                }
                $choice = array_unique($choice, \SORT_NUMERIC);

                if (!$this->_testChoiceMatch($ticket->getDepartmentId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_CATEGORY:
                if (!$this->_testChoiceMatch($ticket->getCategoryId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_PRODUCT:
                if (!$this->_testChoiceMatch($ticket->getProductId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_PRIORITY:
                if (!$this->_testChoiceMatch($ticket->getPriorityId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_WORKFLOW:
                if (!$this->_testChoiceMatch($ticket->getWorkflowId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_ORGANIZATION:
                if (!$this->_testChoiceMatch($ticket->getOrganizationId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_LANGUAGE:
                if (!$this->_testChoiceMatch($ticket->getLanguageId(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_HOLD:
                if (!$this->_testChoiceMatch((int) $ticket->isHold(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_AGENT:
                if (isset($choice['agent_ids'])) {
                    $choice = $choice['agent_ids'];
                }
                if (isset($choice['agent'])) {
                    $choice = $choice['agent'];
                }
                $info = $this->_normalizeAgentChoice($choice);

                $unassigned = $info['unassigned'];
                $agent_ids  = $info['agent_ids'];
                $not_id     = $info['not_id'];

                if ($unassigned) {
                    if ($ticket->getAgentId() && $op == self::OP_IS) {
                        return false;
                    }
                    if (!$ticket->getAgentId() && $op != self::OP_IS) {
                        return false;
                    }
                } else {
                    if ($agent_ids) {
                        if (!$this->_testChoiceMatch($ticket->getAgentId(), $op, $agent_ids)) {
                            return false;
                        }
                    }

                    if ($not_id) {
                        if ($ticket->getAgentId() == $not_id) {
                            return false;
                        }
                    }
                }

                break;

            case self::TERM_AGENT_TEAM:
                if (isset($choice['team_ids'])) {
                    $choice = $choice['team_ids'];
                }
                if (isset($choice['agent_team'])) {
                    $choice = $choice['agent_team'];
                }
                $info     = $this->_normalizeAgentTeamChoice($choice);
                $no_team  = $info['no_team'];
                $team_ids = $info['team_ids'];
                $not_ids  = $info['not_ids'];

                if ($no_team) {
                    if ($ticket->getAgentTeamId() && $op == self::OP_IS) {
                        return false;
                    }
                    if (!$ticket->getAgentTeamId() && $op != self::OP_IS) {
                        return false;
                    }
                } else {
                    if ($team_ids) {
                        if (!$this->_testChoiceMatch($ticket->getAgentTeamId(), $op, $team_ids)) {
                            return false;
                        }
                    }

                    if ($not_ids) {
                        if (!$this->_testChoiceMatch($ticket->getAgentTeamId(), 'not', $not_ids)) {
                            return false;
                        }
                    }
                }

                break;

            case 'time_waiting':
            case self::TERM_USER_WAITING:
                if (!$ticket->getDateUserWaiting()) {
                    return false;
                }

                $choice = $this->normalizeWaitingTime($choice);
                if (is_array($choice) && isset($choice['waiting_time'])) {
                    $time = time() - $ticket->getDateUserWaiting()->getTimestamp();
                    $secs = \Orb\Util\Dates::getUnitInSeconds($choice['waiting_time'], $choice['waiting_time_unit']);

                    if (!$this->_testRangeMatch($time, $op, $secs)) {
                        return false;
                    }
                } else {
                    return false;
                }

                break;

            case self::TERM_PARTICIPANT:

                $info   = $this->_normalizeAgentChoice($choice);
                $choice = (array) $info['agent_ids'];
                if (count($choice) == 1) {
                    $choice = array_pop($choice);
                }

                $participant_ids = $ticket->getParticipantPeopleIds();

                if (is_array($choice)) {
                    $any = false;
                    foreach ($choice as $person_id) {
                        $is_in = in_array($person_id, $participant_ids);

                        if ($is_in) {
                            $any = true;
                            if ($op == self::OP_CONTAINS) {
                                break;
                            } else {
                                return false;
                            }
                        }
                    }

                    if ($op == self::OP_CONTAINS and !$any) {
                        return false;
                    }
                } else {
                    if (in_array($choice, $participant_ids)) {
                        if ($op == self::OP_NOT) {
                            return false;
                        }
                    } else {
                        if ($op == self::OP_IS) {
                            return false;
                        }
                    }
                }
                break;

            case self::TERM_LABEL:

                $choice_labels = [];
                if (!empty($choice['labels'])) {
                    if (!is_array($choice['labels'])) {
                        $choice['labels'] = explode(',', $choice['labels']);
                    }
                    foreach ($choice['labels'] as $l) {
                        $l                 = Strings::utf8_strtolower($l);
                        $choice_labels[$l] = trim($l);
                    }
                }

                $has = false;
                foreach ($ticket->labels as $l) {
                    $l = Strings::utf8_strtolower($l->label);
                    if (isset($choice_labels[$l])) {
                        $has = true;
                        break;
                    }
                }

                if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
                    if (!$has) {
                        return false;
                    }
                } else {
                    if ($has) {
                        return false;
                    }
                }

                break;

            case self::TERM_SUBJECT:
                $choice = (array) $choice;
                $choice = array_pop($choice);

                switch ($op) {
                    case self::OP_IS:
                        if ($ticket->getSubject() != $choice) {
                            return false;
                        }
                        break;
                    case self::OP_NOT:
                        if ($ticket->getSubject() == $choice) {
                            return false;
                        }
                        break;
                    case self::OP_CONTAINS:
                        if (strpos(strtolower($ticket->getSubject()), strtolower($choice)) === false) {
                            return false;
                        }
                        break;
                    case self::OP_NOTCONTAINS:
                        if (strpos(strtolower($ticket->getSubject()), strtolower($choice)) !== false) {
                            return false;
                        }
                        break;
                }
                break;

            case self::TERM_SENT_TO_ADDRESS:
                $choice = (array) $choice;
                $choice = array_pop($choice);

                $choice = strtolower($choice);
                $has    = $ticket->hasSentToAddress($choice);

                switch ($op) {
                    case self::OP_IS:
                    case self::OP_CONTAINS:
                        if (!$has) {
                            return false;
                        }
                        break;
                    case self::OP_NOT:
                    case self::OP_NOTCONTAINS:
                        if ($has) {
                            return false;
                        }
                        break;
                }
                break;

            case self::TERM_CREATION_SYSTEM:
                if (!$this->_testStringMatch($ticket->getCreationSystem(), $op, $choice, true, true)) {
                    return false;
                }
                break;

            case self::TERM_DATE_ARCHIVED:
                if ($ticket->getStatus() != Ticket::STATUS_ARCHIVED) {
                    return false;
                }
                if (!$this->_testDateMatch($ticket->getDateArchived(), $op, $choice)) {
                    return false;
                }
                break;

            case self::TERM_DATE_RESOLVED:
                if (!$ticket->getDateResolved()) {
                    return false;
                }
                if (!$this->_testDateMatch($ticket->getDateResolved(), $op, $choice)) {
                    return false;
                }
                break;
            case self::TERM_DATE_ON_HOLD:
                if (!$ticket->getDateOnHold()) {
                    return false;
                }
                if (!$this->_testDateMatch($ticket->getDateOnHold(), $op, $choice)) {
                    return false;
                }
                break;

            case self::TERM_DATE_LAST_AGENT_REPLY:
                if (!$ticket->getDateLastAgentReply()) {
                    return false;
                }
                if (!$this->_testDateMatch($ticket->getDateLastAgentReply(), $op, $choice)) {
                    return false;
                }
                break;

            case self::TERM_DATE_LAST_REPLY:
                $ts = $ticket->date_created;
                if ($ticket->getDateLastAgentReply() && $ticket->getDateLastAgentReply()->getTimestamp() > $ts) {
                    $ts = $ticket->getDateLastAgentReply();
                }
                if ($ticket->getDateLastUserReply() && $ticket->getDateLastUserReply()->getTimestamp() > $ts) {
                    $ts = $ticket->getDateLastUserReply();
                }
                if (!$this->_testDateMatch($ts, $op, $choice)) {
                    return false;
                }
                break;

            case self::TERM_DATE_LAST_USER_REPLY:
                if (!$ticket->getDateLastUserReply()) {
                    return false;
                }
                if (!$this->_testDateMatch($ticket->getDateLastUserReply(), $op, $choice)) {
                    return false;
                }
                break;
            case 'time_created':
            case 'time_last_user_reply':
                $field = str_replace('time', 'date', $term);

                $f = $ticket[$field];
                if (!$f || !($f instanceof \DateTime)) {
                    return false;
                }
                $ticket_time = clone $f;

                if (!empty($choice['timezone'])) {
                    $ticket_time->setTimezone(new \DateTimeZone($choice['timezone']));
                    $ticket_time = \Orb\Util\Dates::convertToUtcDateTime($ticket_time);
                }

                $time = clone $ticket_time;
                $time->setTime($choice['hour1'], $choice['minute1']);

                switch ($op) {
                    case 'before':
                        return $ticket_time < $time;
                    case 'after':
                        return $ticket_time > $time;
                }

                break;
            case 'day_created':
            case 'day_last_user_reply':
                $field   = str_replace('time', 'date', $term);
                $weekday = $ticket[$field]->format('l');
                $exists  = in_array($weekday, $choice['days']);
                switch ($op) {
                    case 'is':
                        return $exists;
                    case 'not':
                        return !$exists;
                }
                break;

            case self::TERM_PROBLEMS:
                $choice_problems = [];
                if (!empty($choice['problems'])) {
                    if (!is_array($choice['problems'])) {
                        $choice['problems'] = explode(',', $choice['problems']);
                    }
                    $choice_problems = $choice['problems'];
                }

                $has = false;
                foreach ($ticket->getProblems() as $p) {
                    if (in_array($p->id, $choice_problems)) {
                        $has = true;
                        break;
                    }
                }

                if ($op == self::OP_IS || $op == self::OP_CONTAINS) {
                    if (!$has) {
                        return false;
                    }
                } else {
                    if ($has) {
                        return false;
                    }
                }
                break;

            default:
                $terms = new TicketTerms([[
                                              'type'    => $term,
                                              'op'      => $op,
                                              'options' => $choice,
                                          ]]);
                if (!$terms->doesTicketMatch($ticket)) {
                    return false;
                }
                break;
        }

        return true;
    }

    public static function getTableField($term_id)
    {
        switch ($term_id) {
            case self::TERM_DEPARTMENT:
                return 'department_id';
            case self::TERM_AGENT:
                return 'agent_id';
            case self::TERM_AGENT_TEAM:
                return 'agent_team_id';
            case self::TERM_URGENCY:
                return 'urgency';
            case self::TERM_CATEGORY:
                return 'category_id';
            case self::TERM_PRIORITY:
                return 'priority_id';
            case self::TERM_PRODUCT:
                return 'product_id';
            case self::TERM_WORKFLOW:
                return 'workflow_id';
            case self::TERM_LANGUAGE:
                return 'language_id';
            case self::TERM_ORGANIZATION:
                return 'organization_id';
            case self::TERM_USER_WAITING:
                return 'date_user_waiting';
            case self::TERM_TOTAL_USER_WAITING:
                return 'total_user_waiting';
            case self::TERM_DATE_CREATED:
                return 'date_created';
            case self::TERM_BRAND:
                return 'brand_id';
            case 'person':
                return 'person_id';
            default:
                throw new \InvalidArgumentException("Invalid field: $term_id");
        }
    }

    /**
     * @param string $join
     */
    public function addRawJoin($join)
    {
        $this->add_raw_joins[] = $join;
    }

    /**
     * @param string $where
     */
    public function addRawWhere($where)
    {
        $this->add_raw_wheres[] = $where;
    }

    /**
     * @param string $select
     */
    public function addRawSelect($select)
    {
        $this->add_raw_selects[] = $select;
    }

    /**
     * Try to determine whether or not order by/group can be applied to results.
     *
     * This is used for a UI enhancement, and getting perfect accuracy is non-trivial.
     * Additional checks may need to be added to enable this elsewhere.
     *
     * In case of any doubt this should return true as it is better to show the options that are of no effect in cases
     * than to not show it when it is needed.
     *
     * @return bool True if urgency options should be applied
     */
    public function needsUrgency()
    {
        $status = $this->getApplicableStatuses();

        if ($status && !in_array('awaiting_agent', $status)) {
            return false;
        }

        return true;
    }

    /**
     * Get the statuses that this search is matching.
     *
     * @return array
     */
    private function getApplicableStatuses()
    {
        $status = [];

        if ($info = $this->findTerm('status')) {
            list($term, $op, $data) = $info;
            if (isset($data['status'])) {
                $status = $data['status'];
            }

            if (isset($data['options']) && isset($data['options']['status'])) {
                $status = $data['options']['status'];
            }

            if (!is_array($status)) {
                $status = [$status];
            }

            // not means the real applicable statuses are the opposite
            if ($op === self::OP_NOT || $op === self::OP_NOTCONTAINS) {
                $status = array_diff([
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_AWAITING_USER,
                    Ticket::STATUS_RESOLVED,
                    Ticket::STATUS_ARCHIVED,
                    Ticket::STATUS_HIDDEN,
                ], $status);
            }
        }

        // No specific terms added, so means all of them apply
        if (!$status) {
            // all tickets inc archive
            if ($this->is_archive) {
                return [
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_AWAITING_USER,
                    Ticket::STATUS_RESOLVED,
                    Ticket::STATUS_ARCHIVED,
                    Ticket::STATUS_HIDDEN,
                ];
            // just active
            } else {
                return [
                    Ticket::STATUS_AWAITING_AGENT,
                    Ticket::STATUS_AWAITING_USER,
                    Ticket::STATUS_RESOLVED,
                ];
            }
        }

        return $status;
    }
}
