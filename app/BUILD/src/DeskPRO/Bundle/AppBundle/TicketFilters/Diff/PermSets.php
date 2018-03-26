<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;

class PermSets
{
    /**
     * @var AgentContext[]
     */
    private $agentContexts;

    /**
     * @var array
     */
    private $setToAgent;

    /**
     * @var array
     */
    private $agentToSet;

    /**
     * PermSets constructor.
     *
     * @param Agent[] $agentContexts
     */
    public function __construct(array $agentContexts)
    {
        $this->agentContexts = $agentContexts;
    }

    /**
     * Returns an array of distinct agent permissin sets.
     *
     * @return array
     */
    public function getSets()
    {
        $this->initMap();

        return $this->setToAgent;
    }

    /**
     * Lazy inits the maps.
     */
    private function initMap()
    {
        if ($this->setToAgent !== null) {
            return;
        }

        $this->setToAgent = [];
        $this->agentToSet = [];

        foreach ($this->agentContexts as $agent) {
            $setId = $this->getSetId($agent);
            if (!isset($this->setToAgent[$setId])) {
                $this->setToAgent[$setId] = [];
            }

            $this->setToAgent[$setId][]   = $agent;
            $this->agentToSet[$agent->id] = $setId;
        }
    }

    /**
     * @param Agent $agentContext
     *
     * @return string
     */
    private function getSetId(Agent $agentContext)
    {
        if ($agentContext->canViewAll()) {
            return 'all';
        }

        $parts = $agentContext->allowed_departments;
        if ($agentContext->view_assigned) {
            $parts[] = 'other';
        }
        if ($agentContext->view_unassigned) {
            $parts[] = 'un';
        }

        return implode(',', $parts);
    }
}
