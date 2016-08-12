<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class FilterChangeDetector.
 */
class FilterChangeDetector
{
    /**
     * @var \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    private $filters = [];

    /**
     * @var array
     */
    private $filtersTermMap = [];

    /**
     * @var array
     */
    private $filtersAgentsMap = [];

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $agents;

    /**
     * @var array
     */
    private $team_to_agents;

    /**
     * @var bool
     */
    private $extended_log_info = false;

    /**
     * @var array
     */
    private $explicit_filter_scopes = [];

    /**
     * @var bool
     */
    private $disable_cache = false;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param AgentDataService $agentDataService
     */
    public function __construct(EntityManager $em, AgentDataService $agentDataService)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketFilter $filtersRepo */
        $filtersRepo = $em->getRepository(LegacyTicketFilter::class);

        $this->filters = $filtersRepo->getFilters();
        foreach ($this->filters as $filter) {
            $filterId = $filter->getId();
            $agentId  = $filter->getPersonId();

            $this->filtersAgentsMap[$agentId ?: 'global'][$filterId]      = $filter;
            $this->filtersTermMap[$this->getTermsKey($filter)][$filterId] = $filter;
        }

        $this->agents         = [];
        $this->team_to_agents = [];

        $agentToTeams = $agentDataService->getAgentToTeams();
        foreach ($agentDataService->getAgents() as $agentId => $agent) {
            if (!$agent->isActiveAgent()) {
                continue;
            }

            $this->agents[$agentId] = $agent;

            if (isset($agentToTeams[$agentId])) {
                foreach ($agentToTeams[$agentId] as $teamId) {
                    $this->team_to_agents[$teamId][$agentId] = $agent;
                }
            }
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketFilterSubscription $subscriptionRepo */
        $subscriptionRepo = $em->getRepository(TicketFilterSubscription::class);
        $change_subs      = $subscriptionRepo->getSimplePropertyChangeSubscriptions();
        if ($change_subs) {
            foreach ($change_subs as $sub) {
                if (!isset($filters[$sub['filter_id']]) || !$agentDataService->has($sub['person_id'])) {
                    continue;
                }

                $this->addExplicitFilterScope(
                    $filters[$sub['filter_id']],
                    $agentDataService->get($sub['person_id'])
                );
            }
        }

        if (isset($GLOBALS['DP_FILTERCHANGEDETECT_DISABLE_CACHE']) && $GLOBALS['DP_FILTERCHANGEDETECT_DISABLE_CACHE']) {
            $this->disable_cache = true;
        }
    }

    /**
     * Add a filter check for an agent explicitly. Usually this only goes through
     * detection for chagned filters, but sometimes you need to know if a ticket
     * was in an unaffected filter (e.g., for an 'updated' notification).
     *
     * @param LegacyTicketFilter $filter
     * @param Person             $agent
     */
    public function addExplicitFilterScope(LegacyTicketFilter $filter, Person $agent)
    {
        $filterId = $filter->getId();
        if (!isset($this->explicit_filter_scopes[$filterId])) {
            $this->explicit_filter_scopes[$filterId] = ['filter' => $filter, 'scopes' => []];
        }

        $this->explicit_filter_scopes[$filterId]['scopes'][$agent->getId()] = $agent;
    }

    /**
     * @param LegacyTicketFilter[] $affected_filters
     *
     * @return array
     */
    private function buildFilterCheckList(array $affected_filters)
    {
        $check_list = [];

        foreach ($affected_filters as $filter) {
            if ($filter->getSysName() == 'archive_deleted') {
                continue;
            }

            $agentScopes = [];
            if ($filter->isGlobal()) {
                $agentScopes = $this->agents;
            } elseif ($team_id = $filter->getAgentTeamId()) {
                if (isset($this->team_to_agents[$team_id])) {
                    foreach ($this->team_to_agents[$team_id] as $agentId => $agent) {
                        $agentScopes[$agentId] = $agent;
                    }
                }
            } elseif ($person = $filter->getPerson()) {
                $agentScopes[$person->getId()] = $person;
            }

            if (!$agentScopes) {
                continue;
            }

            $check_list[$filter->getId()] = [
                'filter' => $filter,
                'scopes' => $agentScopes,
            ];
        }

        foreach ($this->explicit_filter_scopes as $sub) {
            if (!isset($check_list[$sub['filter']->id])) {
                $check_list[$sub['filter']->id] = $sub;
            }
        }

        return array_values($check_list);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param array                    $forAgentIds
     *
     * @return FilterChangeSet
     */
    public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context = null, array $forAgentIds = null)
    {
        $logger = $context->getLogger();
        $state  = $ticket->getStateChangeRecorder();

        /** @var FilterChangeSet $exist_set */
        $exist_set = null;

        // Use the last change set to use values we have already calculated
        if ($context && $context->getVars()->has('filter_change_set')) {
            $exist_set = $context->getVars()->get('filter_change_set');
            if ($exist_set->getTicket()->getId() != $ticket->getId()) {
                $exist_set = null;
            }
        }

        if ($this->disable_cache) {
            $exist_set = null;
        }

        // If the states are exactly the same, then we might be able to just return the same
        if ($exist_set && $exist_set->getStateId() >= $state->getStateVersion()) {
            return $exist_set;
        }

        if ($exist_set) {
            $logger->info(sprintf('[FilterChangeDetector] Have exist set. Will try to use cached values from last run.'));
        }

        $is_dep_change = false;
        $isNewTicket   = $state->isNewTicket();

        if ($state->hasChangedField('department')) {
            $is_dep_change = true;
        }

        /** @var Ticket $orig_ticket */
        $orig_ticket = $ticket->getOriginalStateClone();
        $new_ticket  = $ticket;

        $scope_counts        = 0;
        $scope_cached_counts = 0;

        /** @var FilterChange[] $changed */
        $changed = [];
        $filters = $this->getFiltersGroupedByTerm($forAgentIds);

        $start   = microtime(true);
        $checker = new AffectedFiltersCheck($ticket, $filters, $logger);

        $affected_filters = $checker->getNewAffectedFilters();

        if ($exist_set) {
            $logger->info(sprintf('[FilterChangeDetector] Affected filters: %d -- Filters with affected changes since last run: %d', count($checker->getAffectedFilters()), count($affected_filters)));
        }

        $logger->info(sprintf('[FilterChangeDetector] Affected filters took %.3fs', microtime(true) - $start));

        $start         = microtime(true);
        $filter_checks = $this->buildFilterCheckList($affected_filters);
        $logger->info(sprintf('[FilterChangeDetector] Build check list took %.3fs', microtime(true) - $start));

        $generic_match_cache  = [];
        $not_cachable_filters = [];

        $logger->info(sprintf('[FilterChangeDetector] Checking %d filters', count($filter_checks)));

        // Calculate who could actually see it
        $start = microtime(true);

        $distinctAgents = [];
        $agentPermCache = [];

        // remove agents from the scopes
        $forAgentIds     = array_combine($forAgentIds, $forAgentIds);
        $oldFilterChecks = $filter_checks;
        $filter_checks   = [];

        foreach ($oldFilterChecks as $filter_check) {
            // if defined agent list then exclude others
            if (null !== $forAgentIds) {
                $filter_check['scopes'] = array_intersect_key($filter_check['scopes'], $forAgentIds);
            }

            $distinctAgents += $filter_check['scopes'];
            $filter_checks[] = $filter_check;
        }

        // calculate agent view permissions
        foreach ($distinctAgents as $agentId => $agent) {
            /* @var Person $agent */
            if (!$agent->isAgent()) {
                $agentPermCache[$agentId] = ['old' => false, 'new' => false];
                continue;
            }

            if ($isNewTicket) {
                $see_old = false;
                $see_new = true;
            } else {
                $see_old = true;
                $see_new = true;
            }

            // testing check
            // there is no mock for the PermissionsManager yet
            if (!defined('DP_BOOT_MODE') || DP_BOOT_MODE != 'testing') {
                /** @var TicketChecker $ticketChecker */
                $ticketChecker = $agent->getPermissionsManager()->TicketChecker;
                if ($see_old && !$ticketChecker->canView($orig_ticket)) {
                    $see_old = false;
                }
                if ($see_new && !$ticketChecker->canView($new_ticket)) {
                    $see_new = false;
                }
            }

            $agentPermCache[$agentId] = ['old' => $see_old, 'new' => $see_new];
        }

        $logger->debug(sprintf('[FilterChangeDetector] Permissions of %d agents calculated in %.3fs', count($agentPermCache), microtime(true) - $start));

        $time = microtime(true);

        foreach ($filter_checks as $filter_check) {
            /* @var LegacyTicketFilter $filter */
            $filter        = $filter_check['filter'];
            $filterId      = $filter->getId();
            $filterSysName = $filter->getSysName();
            $agent_scopes  = [];

            foreach ($filter_check['scopes'] as $a_id => $a) {
                if (isset($agentPermCache[$a_id]) && ($agentPermCache[$a_id]['old'] || $agentPermCache[$a_id]['new'])) {
                    $agent_scopes[$a_id] = $a;
                }
            }

            if (!$agent_scopes) {
                continue;
            }

            $filter_ts = microtime(true);

            $filter_change      = new FilterChange($filter);
            $changed[$filterId] = $filter_change;

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] ----- BEGIN #%d %s -- %d scopes -----', $filterId, $filter->getTitle(), count($agent_scopes)));
            }
            $cached_terms_orig = [];
            $cached_terms_new  = [];

            foreach ($agent_scopes as $agentId => $agent) {
                $agentPermOld = $agentPermCache[$agentId]['old'];
                $agentPermNew = $agentPermCache[$agentId]['new'];

                $orig_match_real = $new_match_real = null;
                $new_match       = $orig_match       = false;

                // RESULT_IS_CACHED
                if (!$this->disable_cache && isset($generic_match_cache[$filterId])) {
                    $pre_orig_match = $generic_match_cache[$filterId]['pre_orig_match'];
                    $pre_new_match  = $generic_match_cache[$filterId]['pre_new_match'];
                    $orig_match     = $generic_match_cache[$filterId]['orig_match'];
                    $new_match      = $generic_match_cache[$filterId]['new_match'];

                    if (!$agentPermOld) {
                        $orig_match = false;
                    }
                    if (!$agentPermNew) {
                        $new_match = false;
                    }

                    if ($pre_orig_match && $agentPermOld) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($pre_new_match && $agentPermNew) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    ++$scope_cached_counts;

                // RESULT_NOT_CACHED
                } else {
                    $reset_status = false;
                    if ($filterSysName) {
                        // System filters are special in that we ignore status/hold
                        // for notifications
                        $searcher = $filter->getSearcher([
                            ['type' => 'status', 'op' => 'ignore'],
                            ['type' => 'hidden_status', 'op' => 'ignore'],
                            ['type' => 'is_hold', 'op' => 'ignore'],
                        ]);

                        // Reset because we have to re-run to get proper result for add/del lists
                        $reset_status = true;
                    } else {
                        $searcher = $filter->getSearcher();
                    }

                    $searcher->setPersonContext($agent);

                    $orig_match_failterm = null;
                    $new_match_failterm  = null;

                    if ($is_dep_change) {
                        if (!$isNewTicket && !$agentPermOld) {
                            $orig_match          = false;
                            $orig_match_failterm = 'ticket.department_id';
                        }

                        if (!$agentPermNew) {
                            $new_match          = false;
                            $new_match_failterm = 'ticket.department_id';
                        }
                    }

                    if ($orig_match_failterm === null) {
                        if ($isNewTicket) {
                            // there is no such thing as an original match with a new ticket
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm, $cached_terms_orig);
                        }
                        $orig_match_real = $orig_match;
                    }

                    if ($new_match_failterm === null) {
                        $new_match      = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm, $cached_terms_new);
                        $new_match_real = $new_match;
                    }

                    if ($orig_match && $agentPermOld) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($new_match && $agentPermNew) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    $pre_orig_match = $orig_match;
                    $pre_new_match  = $new_match;

                    if ($reset_status) {
                        $searcher = $filter->getSearcher();
                        $searcher->setPersonContext($agent);

                        if ($isNewTicket) {
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm, $cached_terms_orig);
                        }

                        $new_match = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm, $cached_terms_new);

                        $orig_match_real = $orig_match;
                        $new_match_real  = $new_match;
                    }

                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] New match: %s -- Orig match: %s', $new_match ? 'yes' : 'no', $orig_match ? 'yes' : 'no'));
                    }
                    if (!$orig_match) {
                        if ($this->extended_log_info) {
                            $logger->debug(sprintf("[FilterChangeDetector] \tOrig failed term: %s", $orig_match_failterm));
                        }
                    }
                    if (!$new_match) {
                        if ($this->extended_log_info) {
                            $logger->debug(sprintf("[FilterChangeDetector] \tNew failed term: %s", $new_match_failterm));
                        }
                    }

                    if ($new_match_real !== null && $orig_match_real !== null && !isset($generic_match_cache[$filterId]) && !isset($not_cachable_filters[$filterId])) {
                        if (!$searcher->needsPersonContext()) {

                            // Two types of matches:

                            // Pre-matches are matches with any special logic
                            // applied to ignore status.
                            // This is required when NEW tickets are created
                            // so we know if a ticket sholud fire events
                            // for a particular filter.
                            // Ex: A new ticket created as RESOLVED wont technically
                            // ever be in 'All tickets' because that fitler has a criteria
                            // for status=awaiting_agent.
                            // But we still want notifications to send for new tickets,
                            // so we have to ignore that status criteria.

                            // Non-pre matches are "real" matches. This is how we determine
                            // where the ticket is *right now*.

                            $generic_match_cache[$filterId] = [
                                'pre_orig_match' => $pre_orig_match,
                                'pre_new_match'  => $pre_new_match,
                                'orig_match'     => $orig_match,
                                'new_match'      => $new_match,
                            ];
                        } else {
                            $not_cachable_filters[$filterId] = $filterId;
                        }
                    }
                } // end RESULT_NOT_CACHED

                if (!$orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both no-match)', $agentId));
                    }
                } elseif ($orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both match)', $agentId));
                    }
                } elseif ($orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: removed from list', $agentId));
                    }
                    $filter_change->removeForAgent($agent);
                } elseif (!$orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: added to list', $agentId));
                    }
                    $filter_change->addForAgent($agent);
                }

                ++$scope_counts;
            }

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] DONE FILTER #%d :: %.4fs', $filterId, microtime(true) - $filter_ts));
            }
        }

        $changed_filters = [];
        foreach ($changed as $fid => $filter_change) {
            if ($filter_change->hasOriginalMatches() || $filter_change->hasNewMatches()) {
                $changed_filters[$fid] = $filter_change;
            }
        }

        $logger->debug(sprintf('[FilterChangeDetector] The following filters could not be optimised: %s', implode(', ', $not_cachable_filters)));
        $logger->info(sprintf('[FilterChangeDetector] Found %d filters in %d iterations (%d of those were cached). Time: %.4fs', count($changed_filters), $scope_counts, $scope_cached_counts, microtime(true) - $time));

        // Add changed filters from previous set
        if ($exist_set) {
            $old_changed_filters = $exist_set->getChangedFilters();
            $copied_ids          = [];
            foreach ($checker->getAffectedFiltersWithNoChanges() as $f) {
                $fid = $f->getId();

                if (isset($old_changed_filters[$fid])) {
                    if (isset($changed_filters[$fid])) {
                        $old_changed_filters[$fid]->merge($changed_filters[$fid]);
                    }
                    $changed_filters[$fid] = $old_changed_filters[$fid];
                    $copied_ids[]          = $fid;
                }
            }

            if ($copied_ids) {
                $logger->info(sprintf('[FilterChangeDetector] Found %d additional filters from previous detection set', count($copied_ids)));
            }
        }

        $set = new FilterChangeSet($ticket, $state->getStateVersion(), $checker->getAffectedFilters(), $changed_filters, $checker->getNewestFieldVersions());

        if ($context) {
            $context->getVars()->set('filter_change_set', $set);
        }

        foreach ($set->getChangedFilters() as $change) {
            $addedAgentIds   = array_keys($change->getAgentsAdded());
            $removedAgentIds = array_keys($change->getAgentsRemoved());

            if ($addedAgentIds || $removedAgentIds) {
                $logger->info(sprintf(
                    '[FilterChangeDetector] Summary: Filter %d -- AddedAgents(%s) -- RemovedAgents(%s)',
                    $change->getFilter()->getId(),
                    implode(', ', $addedAgentIds ?: ['none']),
                    implode(', ', $removedAgentIds ?: ['none'])
                ));
            }
        }

        return $set;
    }

    /**
     * Prepare list of filters grouped by terms.
     *
     * @param array|null $forAgentIds
     *
     * @return LegacyTicketFilter|array
     */
    private function getFiltersGroupedByTerm(array $forAgentIds = null)
    {
        // filter per user filters
        if (null !== $forAgentIds) {
            /** @var LegacyTicketFilter $filters */
            $filters = [];

            // take global filters
            if (isset($this->filtersAgentsMap['global'])) {
                $filters += $this->filtersAgentsMap['global'];
            }

            // take per-user filters
            foreach ($forAgentIds as $agentId) {
                if (isset($this->filtersAgentsMap[$agentId])) {
                    $filters += $this->filtersAgentsMap[$agentId];
                }
            }

            // group them by term
            $groupedFilters = [];
            foreach ($filters as $filter) {
                $groupedFilters[$this->getTermsKey($filter)][$filter->getId()] = $filter;
            }

            return $groupedFilters;
        } else {
            return $this->filtersTermMap;
        }
    }

    /**
     * @param LegacyTicketFilter $filter
     *
     * @return string
     */
    private function getTermsKey(LegacyTicketFilter $filter)
    {
        return md5(serialize($filter->getTerms()));
    }
}
