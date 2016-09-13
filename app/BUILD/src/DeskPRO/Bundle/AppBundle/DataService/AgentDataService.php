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
    protected $agent_timeout = 120;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

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
            s.person_id,
            IF(s.date_last > ?, 1, 0)
            FROM sessions s
            INNER JOIN people p ON (s.person_id = p.id)
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
