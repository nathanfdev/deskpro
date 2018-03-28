<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Component\FilterQueryLanguage;

class TicketFilterEnv
{
    /**
     * @var EnvLoader
     */
    private $loader;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var TicketSqlMatcher
     */
    private $ticketSearcher;

    /**
     * @var TicketMatcher
     */
    private $ticketMatcher;

    /**
     * TicketFilterEnv constructor.
     *
     * @param EnvLoader      $loader
     * @param MatcherFactory $matcherFactory
     */
    public function __construct(EnvLoader $loader, MatcherFactory $matcherFactory)
    {
        $this->loader         = $loader;
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * @param $filterOrId
     *
     * @return FilterQueryLanguage\Query\Query
     */
    public function getFilterQuery($filterOrId)
    {
        if ($filterOrId instanceof Filter) {
            return $filterOrId->query;
        } else {
            $filter = $this->loader->getFilterById($filterOrId);

            return $filter->query;
        }
    }

    /**
     * @param int|Agent $agentOrId
     *
     * @return Context
     */
    public function getAgentContext($agentOrId)
    {
        if ($agentOrId instanceof Agent) {
            $agent = $agentOrId;
        } else {
            $agent = $this->loader->getAgentById($agentOrId);
        }

        return new Context($agent);
    }

    /**
     * @return TicketSearchParams
     */
    public function createSearchParams()
    {
        $searchParams = new TicketSearchParams($this->loader->getTicketFields());

        return $searchParams;
    }

    /**
     * @return TicketSqlMatcher
     */
    public function getSearcher()
    {
        if (!$this->ticketSearcher) {
            $this->ticketSearcher = $this->matcherFactory->createSqlMatcher();
        }

        return $this->ticketSearcher;
    }

    /**
     * @return TicketMatcher
     */
    public function getTicketMatcher()
    {
        if (!$this->ticketMatcher) {
            $this->ticketMatcher = $this->matcherFactory->createMatcher();
        }

        return $this->ticketMatcher;
    }
}
