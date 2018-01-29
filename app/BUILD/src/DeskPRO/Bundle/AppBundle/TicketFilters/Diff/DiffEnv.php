<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Filter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Terms;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;
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
     * @var Agent[]
     */
    private $agents;

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
     * DiffEnv constructor.
     *
     * @param Agent[]  $agents
     * @param Filter[] $filters
     */
    public function __construct(TicketMatcher $ticketMatcher, array $agents, array $filters)
    {
        $this->ticketMatcher = $ticketMatcher;
        $this->agents        = $agents;
        $this->filters       = $filters;
        $this->agentPermSets = new PermSets($this->agents);
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
     * Returns array(Agent[], Agent[], Agent[])
     *
     * @return array
     */
    public function getGroupedAgents()
    {
        return $this->agentPermSets->getSets();
    }

    /**
     * @return Agent[]
     */
    public function getAgents()
    {
        return $this->agents;
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
     * Lazy inits maps.
     */
    private function initTermsMap()
    {
        if ($this->filterFieldsMap !== null) {
            return;
        }

        $this->filterFieldsMap = [];

        foreach ($this->filters as $f) {
            $fields   = [];
            $isUnique = false;

            foreach (new QueryIterator($f->query) as $node) {
                if (!$node instanceof Term) {
                    continue;
                }
                $fields[] = $node->field->identity;

                if (!$isUnique && $this->isUniqueContextTerm($node)) {
                    $isUnique = true;
                }
            }

            $this->filterFieldsMap[$f->id] = $fields;

            if ($isUnique) {
                $this->uniqueContextFilters[] = $f->id;
            }
        }
    }

    /**
     * Check if a term has some value that is unique per-agent, therefore
     * would need to be checked in each agent context.
     *
     * @param Term $term
     *
     * @return bool
     */
    private function isUniqueContextTerm(Term $term)
    {
        foreach (new ValueIterator($term) as $val) {
            if ($val instanceof VarVal) {
                switch ($val->identity) {
                    case 'me':
                    case 'my_teams':
                        return true;
                }
            }
        }

        return false;
    }
}
