<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\LoginLogs;

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class LoginLogs
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     */
    private $agent_data;

    /**
     * @var int
     */
    private $per_page = 50;

    /**
     * @var int
     */
    private $from;

    /**
     * @var Person
     */
    private $filter_agent;

    /**
     * @param EntityManager    $em
     * @param AgentDataService $agent_data
     */
    public function __construct(EntityManager $em, AgentDataService $agent_data)
    {
        $this->em         = $em;
        $this->agent_data = $agent_data;
    }

    /**
     * @param Person $agent
     */
    public function setFilter(Person $agent)
    {
        $this->filter_agent = $agent;
    }

    /**
     * @param int $per_page
     *
     * @return $this
     */
    public function setPerPage($per_page)
    {
        $this->per_page = $per_page;

        return $this;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        if ($page == 0) {
            $page = 1;
        }

        $this->from = ($page - 1) * $this->per_page;

        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $logs = $this->em->getConnection()->fetchAll('
            SELECT login_log.*
            FROM login_log
            LEFT JOIN people ON (people.id = login_log.person_id)
            WHERE people.is_agent
            '.($this->filter_agent ? " AND people.id = {$this->filter_agent->id} " : '')."
            ORDER BY login_log.id DESC
            LIMIT $this->from, $this->per_page
        ");

        foreach ($logs as &$log) {
            $agent = $this->agent_data->get($log['person_id']);
            if ($agent) {
                $log['agent_name'] = $agent->getDisplayName();
            } else {
                $log['agent_name'] = "Agent #{$log['person_id']}";
            }

            $d                      = \DateTime::createFromFormat('Y-m-d H:i:s', $log['date_created']);
            $log['date_created_ts'] = $d->getTimestamp();
        }
        unset($log);

        return $logs;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        $count = $this->em->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM login_log
            LEFT JOIN people ON (people.id = login_log.person_id)
            WHERE people.is_agent
        ');

        return ceil($count / $this->per_page);
    }
}
