<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
     * @param  EntityManager      $em
     * @param  string             $entity_name
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
        if ($this->groups !== null) return;
        $this->groups = $this->repos->findAll();
    }

    /**
     * @return AgentGroups
     */
    public function createAgentGroups()
    {
        $this->preloadGroups();

        return new AgentGroups(array_filter($this->groups, function ($g) { return $g->is_agent_group;}));
    }

    /**
     * @return UserGroups
     */
    public function createUserGroups()
    {
        $this->preloadGroups();

        return new UserGroups(array_filter($this->groups, function ($g) { return !$g->is_agent_group;}));
    }
}
