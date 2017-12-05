<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;

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
     * @param AgentContext[] $agentContexts
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
     * @param AgentContext $agentContext
     *
     * @return string
     */
    private function getSetId(AgentContext $agentContext)
    {
        if ($agentContext->view_all) {
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
