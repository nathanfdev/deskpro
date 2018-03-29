<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Doctrine\ORM\EntityManager;

class AgentTeamDataService
{
    /** @var bool */
    protected $has_init = false;

    /**
     * @var \Application\DeskPRO\Entity\AgentTeam[]
     */
    public $agent_teams = [];

    /**
     * @var int[]
     */
    public $ids = [];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    public static function create(DeskproContainer $container, array $options = null)
    {
        $em = $container->getEm();
        $o  = new static($em);

        return $o;
    }

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    protected function preload()
    {
        if ($this->has_init) {
            return;
        }
        $this->has_init = true;

        $this->agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->getTeams();
        foreach ($this->agent_teams as $a) {
            $this->ids[] = $a->getId();
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\AgentTeam[]
     */
    public function getTeams()
    {
        $this->preload();

        return $this->agent_teams;
    }

    /**
     * @param array $for_ids
     */
    public function getNames(array $for_ids = null)
    {
        $ret = [];

        foreach ($this->getTeams() as $a) {
            if ($for_ids === null || in_array($a->getId(), $for_ids)) {
                $ret[$a->getId()] = $a->getName();
            }
        }

        return $ret;
    }

    /**
     * @return int[]
     */
    public function getIds()
    {
        $this->preload();

        return $this->ids;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\AgentTeam
     */
    public function get($id)
    {
        $this->preload();

        if (isset($this->agent_teams[$id])) {
            return $this->agent_teams[$id];
        }

        return;
    }

    public function __call($name, $args)
    {
        $repos = $this->em->getRepository('DeskPRO:AgentTeam');

        return call_user_func_array([$repos, $name], $args);
    }
}
