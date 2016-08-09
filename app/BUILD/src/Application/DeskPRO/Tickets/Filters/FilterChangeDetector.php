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

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Class FilterChangeDetector.
 */
class FilterChangeDetector
{
    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    private $agents;

    /**
     * @var array
     */
    private $team_to_agents;

    /**
     * @var \Application\DeskPRO\Entity\LegacyTicketFilter[]
     */
    private $filters;

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
     * @param \Application\DeskPRO\Entity\LegacyTicketFilter[] $filters
     * @param \Application\DeskPRO\Entity\Person[]             $agents
     */
    public function __construct(array $filters, array $agents)
    {
        $this->filters        = $filters;
        $this->agents         = [];
        $this->team_to_agents = [];

        foreach ($agents as $agent) {
            if (!($agent->is_agent && !$agent->is_deleted && !$agent->is_disabled)) {
                continue;
            }

            $this->agents[] = $agent;
            $agent->loadHelper('Agent');

            $teams = $agent->getHelper('Agent')->getTeams();
            foreach ($teams as $t) {
                if (!isset($this->team_to_agents[$t->id])) {
                    $this->team_to_agents[$t->id] = [];
                }

                $this->team_to_agents[$t->id][] = $agent;
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
        if (!isset($this->explicit_filter_scopes[$filter->id])) {
            $this->explicit_filter_scopes[$filter->id] = ['filter' => $filter, 'scopes' => []];
        }

        $this->explicit_filter_scopes[$filter->id]['scopes'][] = $agent;
    }

    /**
     * @param array $affected_filters
     *
     * @return array
     */
    private function buildFilterCheckList(array $affected_filters)
    {
        $check_list = [];

        foreach ($affected_filters as $filter) {
            if ($filter->sys_name == 'archive_deleted') {
                continue;
            }

            $agent_scopes = [];
            if ($filter->is_global) {
                $agent_scopes = $this->agents;
            } elseif ($filter->agent_team) {
                $team_id = $filter->agent_team->id;
                if (isset($this->team_to_agents[$team_id])) {
                    foreach ($this->team_to_agents[$team_id] as $agent) {
                        $agent_scopes[] = $agent;
                    }
                }
            } elseif ($filter->person) {
                $agent_scopes[] = $filter->person;
            }

            if (!$agent_scopes) {
                continue;
            }

            $check_list[$filter->id] = [
                'filter' => $filter,
                'scopes' => $agent_scopes,
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
     *
     * @return FilterChangeSet
     */
    public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context = null)
    {
        $logger = $context->getLogger();
        $state  = $ticket->getStateChangeRecorder();

        /** @var FilterChangeSet $exist_set */
        $exist_set = null;

        // Use the last change set to use values we have already calculated
        if ($context && $context->getVars()->has('filter_change_set')) {
            $exist_set = $context->getVars()->get('filter_change_set');
            if ($exist_set->getTicket()->id != $ticket->id) {
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
        $is_new_ticket = $state->isNewTicket();

        if ($state->hasChangedField('department')) {
            $is_dep_change = true;
        }

        $orig_ticket = $ticket->getOriginalStateClone();
        $new_ticket  = $ticket;

        $scope_counts        = 0;
        $scope_cached_counts = 0;

        /** @var FilterChange[] $changed */
        $changed = [];

        $start   = microtime(true);
        $checker = new AffectedFiltersCheck($ticket, $this->filters, $logger);
        if ($exist_set) {
            $checker->setPreviousFieldVersions($exist_set->getFieldVersions());
        }

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
        $agent_perm_cache = [];
        $start            = microtime(true);

        $distinct_agents = [];
        foreach ($filter_checks as $filter_check) {
            foreach ($filter_check['scopes'] as $agent) {
                $distinct_agents[$agent->id] = $agent;
            }
        }

        foreach ($distinct_agents as $agent) {
            /** @var Person $agent */
            if (!$agent->is_agent) {
                $agent_perm_cache[$agent->id] = ['old' => false, 'new' => false];
                continue;
            }

            if ($is_new_ticket) {
                $see_old = false;
                $see_new = true;
            } else {
                $see_old = true;
                $see_new = true;
            }

            // testing check
            // there is no mock for the PermissionsManager yet
            if (!defined('DP_BOOT_MODE') || DP_BOOT_MODE != 'testing') {
                if ($see_old && !$agent->getPermissionsManager()->TicketChecker->canView($orig_ticket)) {
                    $see_old = false;
                }
                if ($see_new && !$agent->getPermissionsManager()->TicketChecker->canView($new_ticket)) {
                    $see_new = false;
                }
            }

            $agent_perm_cache[$agent->id] = ['old' => $see_old, 'new' => $see_new];
        }
        $logger->debug(sprintf('[FilterChangeDetector] Permissions of %d agents calculated in %.3fs', count($agent_perm_cache), microtime(true) - $start));

        $time = microtime(true);
        foreach ($filter_checks as $filter_check) {
            $filter       = $filter_check['filter'];
            $filter_id    = $filter->id;
            $agent_scopes = [];

            foreach ($filter_check['scopes'] as $a) {
                $a_id = $a->id;
                if (isset($agent_perm_cache[$a_id]) && ($agent_perm_cache[$a_id]['old'] || $agent_perm_cache[$a_id]['new'])) {
                    $agent_scopes[$a_id] = $a;
                }
            }

            if (!$agent_scopes) {
                continue;
            }

            $filter_ts = microtime(true);

            $filter_change        = new FilterChange($filter);
            $changed[$filter->id] = $filter_change;

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] ----- BEGIN #%d %s -- %d scopes -----', $filter->id, $filter->title, count($agent_scopes)));
            }
            $cached_terms_orig = [];
            $cached_terms_new  = [];

            foreach ($agent_scopes as $agent_id => $agent) {

                // If the agent couldnt see the old nor the new, then nothing changed
                $agent_perm_old = $agent_perm_cache[$agent_id]['old'];
                $agent_perm_new = $agent_perm_cache[$agent_id]['new'];
                if (!$agent_perm_old && !$agent_perm_new) {
                    continue;
                }

                $orig_match_real = $new_match_real = null;
                $pre_orig_match  = $pre_new_match  = null;
                $new_match       = $orig_match       = false;

                // RESULT_IS_CACHED
                if (!$this->disable_cache && isset($generic_match_cache[$filter_id])) {
                    $pre_orig_match = $generic_match_cache[$filter_id]['pre_orig_match'];
                    $pre_new_match  = $generic_match_cache[$filter_id]['pre_new_match'];
                    $orig_match     = $generic_match_cache[$filter_id]['orig_match'];
                    $new_match      = $generic_match_cache[$filter_id]['new_match'];

                    if (!$agent_perm_old) {
                        $orig_match = false;
                    }
                    if (!$agent_perm_new) {
                        $new_match = false;
                    }

                    if ($pre_orig_match && $agent_perm_old) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($pre_new_match && $agent_perm_new) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    ++$scope_cached_counts;

                // RESULT_NOT_CACHED
                } else {
                    $reset_status = false;
                    if ($filter->sys_name) {
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
                    /* @var \Application\DeskPRO\Searcher\TicketSearch $searcher */
                    $searcher->setPersonContext($agent);

                    $orig_match_failterm = null;
                    $new_match_failterm  = null;

                    if ($is_dep_change) {
                        if (!$is_new_ticket && !$agent_perm_old) {
                            $orig_match          = false;
                            $orig_match_failterm = 'ticket.department_id';
                        }

                        if (!$agent_perm_new) {
                            $new_match          = false;
                            $new_match_failterm = 'ticket.department_id';
                        }
                    }

                    if ($orig_match_failterm === null) {
                        if ($is_new_ticket) {
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

                    if ($orig_match && $agent_perm_old) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($new_match && $agent_perm_new) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    $pre_orig_match = $orig_match;
                    $pre_new_match  = $new_match;

                    if ($reset_status) {
                        $searcher = $filter->getSearcher();
                        $searcher->setPersonContext($agent);

                        if ($is_new_ticket) {
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

                    if ($new_match_real !== null && $orig_match_real !== null && !isset($generic_match_cache[$filter_id]) && !isset($not_cachable_filters[$filter_id])) {
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

                            $generic_match_cache[$filter_id] = [
                                'pre_orig_match' => $pre_orig_match,
                                'pre_new_match'  => $pre_new_match,
                                'orig_match'     => $orig_match,
                                'new_match'      => $new_match,
                            ];
                        } else {
                            $not_cachable_filters[$filter_id] = $filter_id;
                        }
                    }
                } // end RESULT_NOT_CACHED

                if (!$orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both no-match)', $agent_id));
                    }
                } elseif ($orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both match)', $agent_id));
                    }
                } elseif ($orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: removed from list', $agent_id));
                    }
                    $filter_change->removeForAgent($agent);
                } elseif (!$orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: added to list', $agent_id));
                    }
                    $filter_change->addForAgent($agent);
                }

                ++$scope_counts;
            }

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] DONE FILTER #%d :: %.4fs', $filter_id, microtime(true) - $filter_ts));
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
                if (isset($old_changed_filters[$f->id])) {
                    if (isset($changed_filters[$f->id])) {
                        $old_changed_filters[$f->id]->merge($changed_filters[$f->id]);
                    }
                    $changed_filters[$f->id] = $old_changed_filters[$f->id];
                    $copied_ids[]            = $f->id;
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
            $added_aids = [];
            foreach ($change->getAgentsAdded() as $a) {
                $added_aids[] = $a->getId();
            }
            $removed_aids = [];
            foreach ($change->getAgentsRemoved() as $a) {
                $removed_aids[] = $a->getId();
            }

            if ($added_aids || $removed_aids) {
                $logger->info(sprintf(
                    '[FilterChangeDetector] Summary: Filter %d -- AddedAgents(%s) -- RemovedAgents(%s)',
                    $change->getFilter()->id,
                    implode(', ', $added_aids ?: ['none']),
                    implode(', ', $removed_aids ?: ['none'])
                ));
            }
        }

        return $set;
    }
}
