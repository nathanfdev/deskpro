<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class AgentDataService extends AbstractDataService
{
    /** @var array|null */
    protected $online_agent_ids;

    protected $agents_online_status;

    /**
     * @var int
     */
    protected $agent_timeout = 360;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
        $this->db = $this->em->getConnection();
    }

    /**
     * Get an array of agents who are online now (have active sessions).
     *
     * @return int[]
     */
    public function getOnlineAgentIds()
    {
        if ($this->online_agent_ids !== null) {
            return $this->online_agent_ids;
        }
        $cutoff = date('Y-m-d H:i:s', time() - $this->agent_timeout);

        $this->online_agent_ids = $this->db->fetchAllKeyValue('
            SELECT DISTINCT s.person_id
            FROM sessions s
            INNER JOIN people p ON (s.person_id = p.id)
            WHERE p.is_agent = 1 AND p.is_deleted = 0 AND s.date_last > ?
        ', [$cutoff], [], 0, 0);

        return $this->online_agent_ids;
    }

    /**
     * Check if an agent is online.
     *
     * @param int|Person $id_or_agent
     *
     * @return bool
     */
    public function isAgentOnline($id_or_agent)
    {
        $this->getOnlineAgentIds();
        $id = is_object($id_or_agent) ? $id_or_agent->getId() : $id_or_agent;

        return isset($this->online_agent_ids[$id]);
    }

    public function getLastSeen($id_or_agent)
    {
        $id        = is_object($id_or_agent) ? $id_or_agent->getId() : $id_or_agent;
        $last_seen = $this->db->fetchAllKeyValue('
            SELECT DISTINCT s.person_id, s.date_last
            FROM sessions s
            WHERE s.person_id = ?
        ', [$id]);

        return (is_array($last_seen)) ? array_shift($last_seen) : false;
    }

    public function getAgentsOnlineStatus()
    {
        if ($this->agents_online_status !== null) {
            return $this->agents_online_status;
        }

        $cutoff = date('Y-m-d H:i:s', time() - $this->agent_timeout);

        $data = $this->db->fetchAllKeyValue('
            SELECT DISTINCT
            p.id,
            IF(s.date_last < ? OR s.person_id IS NULL, 0, 1)
            FROM people p
            LEFT JOIN sessions s ON (s.person_id = p.id)
            WHERE p.is_agent = 1 AND p.is_deleted = 0
        ', [$cutoff], [], 0, 1);

        $this->agents_online_status = [
            'online' => array_values(array_filter(array_keys($data), function ($value) use ($data) {
                return $data[$value];
            })),
            'offline' => array_values(array_filter(array_keys($data), function ($value) use ($data) {
                return !$data[$value];
            })),
        ];

        return $this->agents_online_status;
    }
}
