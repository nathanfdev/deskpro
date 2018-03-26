<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\EntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterSetRepository;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Component\FilterQueryLanguage;
use DeskPRO\Component\Util\ListUtils;

class Loader
{
    /**
     * @var TicketFilterSetRepository
     */
    private $filterSetEntRepos;

    /**
     * @var EntityRepository\Person
     */
    private $agentRepos;

    /**
     * @var FilterQueryLanguage\Parser
     */
    private $queryParser;

    /**
     * Filter models (not filter entities).
     *
     * @var Filter
     */
    private $filters;

    /**
     * @var Agent[]
     */
    private $agents;

    /**
     * Loader constructor.
     *
     * @param TicketFilterSetRepository  $filterSetEntRepos
     * @param EntityRepository\Person    $agentRepos
     * @param FilterQueryLanguage\Parser $queryParser
     */
    public function __construct(TicketFilterSetRepository $filterSetEntRepos, EntityRepository\Person $agentRepos, FilterQueryLanguage\Parser $queryParser)
    {
        $this->filterSetEntRepos = $filterSetEntRepos;
        $this->agentRepos        = $agentRepos;
        $this->queryParser       = $queryParser;
    }

    /**
     * @return Filter[]
     */
    public function getFilters()
    {
        if ($this->filters !== null) {
            return $this->filters;
        }

        $this->filters = [];

        foreach ($this->filterSetEntRepos->getSetsWithFiltersAndAgents() as $set) {
            foreach ($set->getFilters() as $filterEnt) {
                $filter        = new Filter();
                $filter->id    = $filterEnt->getId();
                $filter->query = $this->queryParser->parseQuery($filterEnt->getQuery());

                if ($set->isGlobal()) {
                    $filter->agents = ListUtils::map($this->getAgents(), function ($a) {
                        return $a->id;
                    });
                } else {
                    foreach ($set->getSharedAgents() as $aEnt) {
                        $filter->agents[] = $aEnt->getId();
                    }
                    foreach ($set->getSharedTeams() as $tEnt) {
                        $filter->agents = array_merge($filter->agents, ListUtils::filterMap($this->getAgents(), function ($a) use ($tEnt) {
                            if (in_array($tEnt->getId(), $a->teams)) {
                                return $a->id;
                            }
                        }));
                    }
                }

                $this->filters[] = $filter;
            }
        }

        return $this->filters;
    }

    /**
     * @param int $id
     *
     * @return null|Filter
     */
    public function getFilterById($id)
    {
        return ListUtils::first($this->getFilters(), function ($f) use ($id) {
            return $f->id == $id;
        });
    }

    /**
     * @return Agent[]
     */
    public function getAgents()
    {
        if ($this->agents !== null) {
            return $this->agents;
        }

        $this->agents = [];

        foreach ($this->agentRepos->getAgents() as $agentEnt) {
            $agent        = new Agent();
            $agent->id    = $agentEnt->getId();
            $agent->teams = $agentEnt->getTeamIds();

            $p = $agentEnt->getPrimaryTeam();
            if ($p) {
                $agent->primary_team = $p->getId();
            }

            $agentEnt->loadHelper('Agent');
            $agentEnt->loadHelper('AgentPermissions');
            $agentEnt->loadHelper('PermissionsManager', ['force_load_usergroups' => true]);

            $agent->view_unassigned     = $agentEnt->hasPerm('agent_tickets.view_unassigned');
            $agent->view_assigned       = $agentEnt->hasPerm('agent_tickets.view_others');
            $agent->allowed_departments = $agentEnt->getHelper('AgentPermissions')->getAllowedDepartments('tickets');

            if (empty($agentEnt->getHelper('AgentPermissions')->getDisallowedDepartments('tickets'))) {
                $agent->all_departments_allowed = true;
            }

            $this->agents[] = $agent;
        }

        return $this->agents;
    }

    /**
     * @param int $id
     *
     * @return null|Agent
     */
    public function getAgentById($id)
    {
        return ListUtils::first($this->getAgents(), function ($a) use ($id) {
            return $a->id == $id;
        });
    }
}
