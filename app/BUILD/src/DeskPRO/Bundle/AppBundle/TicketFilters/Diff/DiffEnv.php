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
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Terms;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\ScalarVal;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\VarVal;
use DeskPRO\Component\FilterQueryLanguage\QueryIterator;
use DeskPRO\Component\FilterQueryLanguage\ValueIterator;
use DeskPRO\Component\Util\ListUtils;

class DiffEnv
{
    /**
     * @var TicketMatcher
     */
    private $ticketMatcher;

    /**
     * @var AgentContext[]
     */
    private $agentContexts;

    /**
     * @var
     */
    private $agentPermSets;

    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * Array of fid => fields.
     *
     * @var array
     */
    private $filterFieldsMap;

    /**
     * List of filters that contain terms making them unique per agent.
     *
     * @var int[]
     */
    private $uniqueContextFilters;

    /**
     * @var List of filters that have a term on status being awaiting agent
     */
    private $awaitingAgentFilters;

    /**
     * DiffEnv constructor.
     *
     * @param AgentContext[] $agentContexts
     * @param Filter[]       $filters
     */
    public function __construct(TicketMatcher $ticketMatcher, array $agentContexts, array $filters)
    {
        $this->ticketMatcher = $ticketMatcher;
        $this->agentContexts = $agentContexts;
        $this->filters       = $filters;
        $this->agentPermSets = new PermSets($this->agentContexts);
    }

    /**
     * Gets an array of fitlers that contain at least one of the fields
     * in $checkFields.
     *
     * @param array $checkFields
     *
     * @return Filter[]
     */
    public function getFiltersWithAnyField(array $checkFields)
    {
        $this->initTermsMap();
        $filters = [];

        foreach ($this->filters as $f) {
            $filterFields = $this->filterFieldsMap[$f->id];
            if (ListUtils::containsAny($filterFields, $checkFields)) {
                $filters[] = $f;
            }
        }

        return $filters;
    }

    /**
     * Gets agents grouped by their common permission sets.
     *
     * Returns array(AgentContext[], AgentContext[], AgentContext[])
     *
     * @return array
     */
    public function getGroupedAgentContexts()
    {
        return $this->agentPermSets->getSets();
    }

    /**
     * @return AgentContext[]
     */
    public function getAgentContexts()
    {
        return $this->agentContexts;
    }

    /**
     * @return TicketMatcher
     */
    public function getTicketMatcher()
    {
        return $this->ticketMatcher;
    }

    /**
     * @param int $filterId
     *
     * @return bool
     */
    public function isFilterContextUnique($filterId)
    {
        $this->initTermsMap();

        return in_array($filterId, $this->uniqueContextFilters);
    }

    /**
     * Check if a filter has a critera making it only match awaiting agent.
     *
     * @param int $filterId
     *
     * @return bool
     */
    public function isFilterAwaitingAgent($filterId)
    {
        $this->initTermsMap();

        return in_array($filterId, $this->awaitingAgentFilters);
    }

    /**
     * Lazy inits maps.
     */
    private function initTermsMap()
    {
        if ($this->filterFieldsMap !== null) {
            return;
        }

        $this->filterFieldsMap = [];

        foreach ($this->filters as $f) {
            $fields          = [];
            $isUnique        = false;
            $isAwaitingAgent = false;

            foreach (new QueryIterator($f->query) as $node) {
                if (!$node instanceof Term) {
                    continue;
                }
                $fields[] = $node->field->identity;

                // If we havent calculated the is* flags yet,
                // we need ot iterate ove values to determine if they need to be set
                // (this outer if check is just to avoid the iterator if we already have the flags)
                if (!$isUnique || (!$isAwaitingAgent && $node->field->identity === Terms::TICKET_STATUS)) {
                    foreach (new ValueIterator($node) as $val) {

                        // Check if its a unique term
                        if (!$isUnique && $val instanceof VarVal) {
                            switch ($val->identity) {
                                case 'me':
                                case 'my_teams':
                                    $isUnique = true;
                            }
                        }

                        // Check if its a filter based on awaiting agent
                        if (!$isAwaitingAgent
                            && $node->field->identity === Terms::TICKET_STATUS
                            && $val instanceof ScalarVal
                            && $val->value === 'awaiting_agent'
                            && ($node->operator->getOperator() === Query::OP_EQ || $node->operator->getOperator() === Query::OP_IN)
                        ) {
                            $isAwaitingAgent = true;
                        }
                    }
                }
            }

            $this->filterFieldsMap[$f->id] = $fields;

            if ($isUnique) {
                $this->uniqueContextFilters[] = $f->id;
            }
            if ($isAwaitingAgent) {
                $this->awaitingAgentFilters[] = $f->id;
            }
        }
    }
}
