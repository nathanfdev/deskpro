<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DBAL\Connection;

class DetectSearchTableErrors
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var int
     */
    protected $limit = 10000;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return int $limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function outputErrors($errors = null)
    {
        if ($errors === null) {
            $errors = $this->getErrors();
        }

        foreach ($errors as $error) {
            switch ($error['type']) {
                case 'missing_real_id':
                    echo "[missing_search_id] #{$error['ticket_id']} is in search but not real\n";
                    break;

                case 'missing_search_id':
                    echo "[missing_search_id] #{$error['ticket_id']} is in real but not search\n";
                    break;

                case 'data_mismatch':
                    echo "[data_mismatch] #{$error['ticket_id']} contains different data in search and real\n";
                    foreach ($error['mismatch'] as $k => $v) {
                        echo "\t[$k]\n";
                        echo "\t\tsearch: ".$v['search']."\n";
                        echo "\t\treal:   ".$v['real']."\n";
                    }
                    break;
            }
        }
    }

    public function errorsAsString($errors = null)
    {
        ob_start();
        $this->outputErrors($errors);
        $string = ob_get_clean();

        return $string;
    }

    public function getErrors()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', -1);

        $errors = [];

        //------------------------------
        // Select data
        //------------------------------

        $select_fields = '
            `id`,
            `language_id`,
            `brand_id`,
            `department_id`,
            `category_id`,
            `priority_id`,
            `workflow_id`,
            `product_id`,
            `person_id`,
            `agent_id`,
            `agent_team_id`,
            `organization_id`,
            `email_account_id`,
            `creation_system`,
            `status`,
            `urgency`,
            `ticket_status_id`,
            `date_created`,
            `date_first_agent_reply`,
            `date_last_agent_reply`,
            `date_last_user_reply`,
            `date_agent_waiting`,
            `date_user_waiting`,
            `total_user_waiting`,
            `total_to_first_reply`
        ';

        $search_tickets = [];
        $q              = $this->db->executeQuery("
            SELECT $select_fields
            FROM tickets_search_active
            ORDER BY id DESC
            LIMIT {$this->limit}
        ");
        while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
            $search_tickets[$r['id']] = $r;
        }
        $q->closeCursor();

        $real_tickets = [];
        $q            = $this->db->executeQuery("
            SELECT $select_fields
            FROM tickets
            WHERE status IN ('awaiting_user', 'awaiting_agent', 'resolved')
            ORDER BY id DESC
            LIMIT {$this->limit}
        ");
        while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
            $real_tickets[$r['id']] = $r;
        }
        $q->closeCursor();

        //------------------------------
        // Check for bad tickets
        //------------------------------

        foreach ($search_tickets as $tid => $info) {
            if (!isset($real_tickets[$tid])) {
                $errors[] = [
                    'type'      => 'missing_real_id',
                    'ticket_id' => $tid,
                    'msg'       => 'in search but not real',
                ];
            } else {
                $mismatch = [];
                foreach ($info as $k => $v) {
                    if ($real_tickets[$tid][$k] != $v) {
                        $mismatch[$k] = [
                            'real'   => $real_tickets[$tid][$k],
                            'search' => $v,
                        ];
                    }
                }

                if ($mismatch) {
                    $errors[] = [
                        'type'      => 'data_mismatch',
                        'ticket_id' => $tid,
                        'msg'       => 'contains different data in search and real',
                        'mismatch'  => $mismatch,
                    ];
                }
            }
        }

        foreach ($real_tickets as $tid => $info) {
            if (!isset($search_tickets[$tid])) {
                $errors[] = [
                    'type'      => 'missing_search_id',
                    'ticket_id' => $tid,
                    'msg'       => 'in real but not search',
                ];
            }
        }

        unset($search_tickets);
        unset($real_tickets);

        return $errors;
    }
}
