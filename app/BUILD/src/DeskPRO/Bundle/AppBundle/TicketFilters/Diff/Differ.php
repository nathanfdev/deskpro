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

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Component\Util\ListUtils;

class Differ
{
    /**
     * @var DiffEnv
     */
    private $diffEnv;

    /**
     * Differ constructor.
     *
     * @param DiffEnv $diffEnv
     */
    public function __construct(DiffEnv $diffEnv)
    {
        $this->diffEnv = $diffEnv;
    }

    /**
     * @param TicketChange $ticketChange
     *
     * @return FilterOp[]
     */
    public function getFilterChangeOperations(TicketChange $ticketChange)
    {
        $ticketA = $ticketChange->getTicketA();
        $ticketB = $ticketChange->getTicketB();

        $matcher         = $this->diffEnv->getTicketMatcher();
        $changedFields   = $ticketChange->getChangedFields();
        $affectedFilters = $this->diffEnv->getFiltersWithAnyField($changedFields);

        if (empty($affectedFilters)) {
            return [];
        }

        $affectedAgentIds = [];
        foreach ($affectedFilters as $f) {
            $affectedAgentIds = array_merge($affectedAgentIds, $f->agents);
        }
        $affectedAgentIds = ListUtils::unique($affectedAgentIds);

        $agentSets = $this->getAgentSets($ticketChange, $affectedAgentIds);

        $filterOps = [];
        foreach ($affectedFilters as $filter) {
            $filterOp = new FilterOp($filter->id);

            foreach ($agentSets as $agentSet) {
                $agentContexts = ListUtils::filter($agentSet['agentContexts'], function ($a) use ($filter) {
                    return in_array($a->getAgentId(), $filter->agents);
                });
                if (empty($agentContexts)) {
                    continue;
                }

                //------------------------------
                // Filter contains context-specific terms
                //------------------------------

                if ($this->diffEnv->isFilterContextUnique($filter->id)) {
                    foreach ($agentContexts as $agentContext) {
                        if ($agentSet['viewBefore']) {
                            $matchBefore = $matcher->doesQueryMatch($filter->query, $ticketA, $agentContext);
                        } else {
                            $matchBefore = false;
                        }

                        if ($agentSet['viewAfter']) {
                            $matchAfter = $matcher->doesQueryMatch($filter->query, $ticketB, $agentContext);
                        } else {
                            $matchAfter = false;
                        }

                        if ($matchBefore && !$matchAfter) {
                            $filterOp->addDelAgentId($agentContext->getAgentId());
                        } elseif (!$matchBefore && $matchAfter) {
                            $filterOp->addAddAgentId($agentContext->getAgentId());
                        }
                        if ($matchBefore) {
                            $filterOp->addBeforeMatchAgentId($agentContext->getAgentId());
                        }
                        if ($matchAfter) {
                            $filterOp->addAfterMatchAgentId($agentContext->getAgentId());
                        }
                    }

                //------------------------------
                // Common terms, we only need to run it once per group
                //------------------------------
                } else {
                    $agentContext = ListUtils::first($agentContexts);
                    if ($agentSet['viewBefore']) {
                        $matchBefore = $matcher->doesQueryMatch($filter->query, $ticketA, $agentContext);
                    } else {
                        $matchBefore = false;
                    }

                    if ($agentSet['viewAfter']) {
                        $matchAfter = $matcher->doesQueryMatch($filter->query, $ticketB, $agentContext);
                    } else {
                        $matchAfter = false;
                    }

                    // Apply this result to all agents in the group
                    foreach ($agentContexts as $agentContext) {
                        if ($matchBefore && !$matchAfter) {
                            $filterOp->addDelAgentId($agentContext->getAgentId());
                        } elseif (!$matchBefore && $matchAfter) {
                            $filterOp->addAddAgentId($agentContext->getAgentId());
                        }
                        if ($matchBefore) {
                            $filterOp->addBeforeMatchAgentId($agentContext->getAgentId());
                        }
                        if ($matchAfter) {
                            $filterOp->addAfterMatchAgentId($agentContext->getAgentId());
                        }
                    }
                }
            }

            if (!empty($filterOp->getAddAgentIds()) || !empty($filterOp->getDelAgentIds())) {
                $filterOps[$filter->id] = $filterOp;
            }
        }

        return $filterOps;
    }

    /**
     * Sorts agents into groups with similar permissions.
     *
     * @param TicketChange $ticketChange
     * @param int[]        $affectedAgentIds
     *
     * @return array
     */
    private function getAgentSets(TicketChange $ticketChange, $affectedAgentIds)
    {
        $ticketA      = $ticketChange->getTicketA();
        $ticketB      = $ticketChange->getTicketB();
        $isPermChange = $ticketChange->isPermChange();

        // turn it into a map for faster lookups
        $affectedAgentIds = array_fill_keys($affectedAgentIds, true);

        $agentSetsByPerm = [
            'seeBoth'       => [],
            'seeBeforeOnly' => [],
            'seeAfterOnly'  => [],
        ];

        foreach ($this->diffEnv->getGroupedAgents() as $group) {
            $group = ListUtils::filter($group, function (Agent $a) use ($affectedAgentIds) {
                return isset($affectedAgentIds[$a->id]);
            });

            if (empty($group)) {
                continue;
            }

            // The group all share perms, so
            // the first dictates the perm of all agents in the group
            /** @var AgentContext $first */
            $first = ListUtils::first($group);

            $canSeeAfter = $first->canViewTicket($ticketB);

            if (!$ticketA->id) { // new ticket
                $canSeeBefore = false;
            } elseif (!$isPermChange) {
                $canSeeBefore = $canSeeAfter;
            } else {
                $canSeeBefore = $first->canViewTicket($ticketA);
            }

            if ($canSeeBefore && $canSeeAfter) {
                $agentSetsByPerm['seeBoth'][] = $group;
            } elseif ($canSeeBefore && !$canSeeAfter) {
                $agentSetsByPerm['seeBeforeOnly'][] = $group;
            } elseif ($canSeeAfter && !$canSeeBefore) {
                $agentSetsByPerm['seeAfterOnly'][] = $group;
            }
        }

        $agentSets = [];
        if (!empty($agentSetsByPerm['seeBoth'])) {
            $agentSets[] = [
                'viewBefore'    => true,
                'viewAfter'     => true,
                'agentContexts' => ListUtils::map(ListUtils::flatten($agentSetsByPerm['seeBoth']), [Context::class, 'createContext']),
            ];
        }
        if (!empty($agentSetsByPerm['seeBeforeOnly'])) {
            $agentSets[] = [
                'viewBefore'    => true,
                'viewAfter'     => false,
                'agentContexts' => ListUtils::map(ListUtils::flatten($agentSetsByPerm['seeBeforeOnly']), [Context::class, 'createContext']),
            ];
        }
        if (!empty($agentSetsByPerm['seeAfterOnly'])) {
            $agentSets[] = [
                'viewBefore'    => true,
                'viewAfter'     => false,
                'agentContexts' => ListUtils::map(ListUtils::flatten($agentSetsByPerm['seeAfterOnly']), [Context::class, 'createContext']),
            ];
        }

        return $agentSets;
    }
}
