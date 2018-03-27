<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Groups;

use Application\DeskPRO\EntityRepository\Usergroup as UsergroupRepository;
use Application\DeskPRO\People\AgentGroups;
use Application\DeskPRO\People\UserGroups;
use Doctrine\ORM\EntityManager;

class GroupsReposFactory
{
    /**
     * @var UsergroupRepository
     */
    private $repos;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    private $groups;

    /**
     * @param EntityManager $em
     * @param string        $entity_name
     *
     * @return GroupsReposFactory
     */
    public static function createFromEntityManager(EntityManager $em, $entity_name = 'DeskPRO:Usergroup')
    {
        return new self($em->getRepository($entity_name));
    }

    /**
     * @param UsergroupRepository $repos
     */
    public function __construct(UsergroupRepository $repos)
    {
        $this->repos = $repos;
    }

    private function preloadGroups()
    {
        if ($this->groups !== null) {
            return;
        }
        $this->groups = $this->repos->findAll();
    }

    /**
     * @return AgentGroups
     */
    public function createAgentGroups()
    {
        $this->preloadGroups();

        return new AgentGroups(array_filter($this->groups, function ($g) {
            return $g->is_agent_group;
        }));
    }

    /**
     * @return UserGroups
     */
    public function createUserGroups()
    {
        $this->preloadGroups();

        return new UserGroups(array_filter($this->groups, function ($g) {
            return !$g->is_agent_group;
        }));
    }
}
