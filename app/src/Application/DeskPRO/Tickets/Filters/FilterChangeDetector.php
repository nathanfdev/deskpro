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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;


use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Monolog\Logger;

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
     * @var \Application\DeskPRO\Entity\TicketFilter[]
     */
    private $filters;

    /**
     * @var bool
     */
    private $extended_log_info = false;

    /**
     * @var array
     */
    private $explicit_filter_scopes = array();

    /**
     * @var bool
     */
    private $disable_cache = false;

    /**
     * @param \Application\DeskPRO\Entity\TicketFilter[] $filters
     * @param \Application\DeskPRO\Entity\Person[]       $agents
     */
    public function __construct(array $filters, array $agents)
    {
        $this->filters = $filters;
        $this->agents  = $agents;

        $this->team_to_agents = array();
        foreach ($this->agents as $agent) {
            $agent->loadHelper('Agent');

            $teams = $agent->getHelper('Agent')->getTeams();
            foreach ($teams as $t) {
                if (!isset($this->team_to_agents[$t->id])) {
                    $this->team_to_agents[$t->id] = array();
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
     * @param TicketFilter $filter
     * @param Person       $agent
     */
    public function addExplicitFilterScope(TicketFilter $filter, Person $agent)
    {
        if (!isset($this->explicit_filter_scopes[$filter->id])) {
            $this->explicit_filter_scopes[$filter->id] = array('filter' => $filter, 'scopes' => array());
        }

        $this->explicit_filter_scopes[$filter->id]['scopes'][] = $agent;
    }


    /**
     * @param  array $affected_filters
     * @return array
     */
    private function buildFilterCheckList(array $affected_filters)
    {
        $check_list = array();

        foreach ($affected_filters as $filter) {
            if ($filter->sys_name == 'archive_deleted') {
                continue;
            }

            $agent_scopes = array();
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

            $check_list[$filter->id] = array(
                'filter' => $filter,
                'scopes' => $agent_scopes
            );
        }

        foreach ($this->explicit_filter_scopes as $sub) {
            if (!isset($check_list[$sub['filter']->id])) {
                $check_list[$sub['filter']->id] = $sub;
            }
        }

        return array_values($check_list);
    }


    /**
     * @param  Ticket                   $ticket
     * @param  ExecutorContextInterface $context
     * @return FilterChangeSet
     */
    public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context = null)
    {
        $logger = $context->getLogger();
        $state = $ticket->getStateChangeRecorder();

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
            $logger->info(sprintf("[FilterChangeDetector] Have exist set. Will try to use cached values from last run."));
        }

        $old_dep_id = null;
        $new_dep_id = null;
        $is_dep_change = false;
        $is_new_ticket = $state->isNewTicket();

        if ($state->hasChangedField('department')) {
            $old_dep = $state->getOriginalValueForField('department');
            $is_dep_change = true;

            if ($old_dep) {
                $old_dep_id = $old_dep->id;
            }
            if ($ticket->department) {
                $new_dep_id = $ticket->department->id;
            }
        }

        $orig_ticket = $ticket->getOriginalStateClone();
        $new_ticket  = $ticket;

        $scope_counts = 0;
        $scope_cached_counts = 0;

        /** @var FilterChange[] $changed */
        $changed = array();

        $start = microtime(true);
        $checker = new AffectedFiltersCheck($ticket, $this->filters, $logger);
        if ($exist_set) {
            $checker->setPreviousFieldVersions($exist_set->getFieldVersions());
        }

        $affected_filters = $checker->getNewAffectedFilters();

        if ($exist_set) {
            $logger->info(sprintf("[FilterChangeDetector] Affected filters: %d -- Filters with affected changes since last run: %d", count($checker->getAffectedFilters()), count($affected_filters)));
        }

        $logger->info(sprintf("[FilterChangeDetector] Affected filters took %.3fs", microtime(true)-$start));

        $start = microtime(true);
        $filter_checks = $this->buildFilterCheckList($affected_filters);
        $logger->info(sprintf("[FilterChangeDetector] Build check list took %.3fs", microtime(true)-$start));

        $generic_match_cache = array();
        $not_cachable_filters = array();

        $logger->info(sprintf("[FilterChangeDetector] Checking %d filters", count($filter_checks)));

        // Calculate who could actually see it
        $agent_perm_cache = array();
        $start = microtime(true);
        foreach ($filter_checks as $filter_check) {
            foreach ($filter_check['scopes'] as $agent) {

                // Already done checks in a previous iteration
                if (isset($agent_perm_cache[$agent->id])) {
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
                    if ($agent->isHelperLoader('PermissionsManager')) {
                        if ($see_old && !$agent->PermissionsManager->TicketChecker->canView($orig_ticket)) {
                            $see_old = false;
                        }
                        if ($see_new && !$agent->PermissionsManager->TicketChecker->canView($new_ticket)) {
                            $see_new = false;
                        }
                    }
                }

                $agent_perm_cache[$agent->id] = array('old' => $see_old, 'new' => $see_new);
            }
        }
        $logger->debug(sprintf("[FilterChangeDetector] Permissions of %d agents calculated in %.3fs", count($agent_perm_cache), microtime(true)-$start));

        $time = microtime(true);
        foreach ($filter_checks as $filter_check) {

            $filter       = $filter_check['filter'];
            $agent_scopes = $filter_check['scopes'];

            $filter_ts = microtime(true);

            $filter_change = new FilterChange($filter);
            $changed[$filter->id] = $filter_change;

            if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] ----- BEGIN #%d %s -- %d scopes -----", $filter->id, $filter->title, count($agent_scopes)));

            foreach ($agent_scopes as $agent) {

                // A filter could belong to an agent that isn't an agent anymore
                if (!$agent->is_agent) {
                    continue;
                }

                // Or the agent might be soft deleted, in which case we sholudnt waste time
                if ($agent->is_deleted || $agent->is_disabled) {
                    continue;
                }

                // If the agent couldnt see the old nor the new, then nothing changed
                $agent_perm_old = $agent_perm_cache[$agent->id]['old'];
                $agent_perm_new = $agent_perm_cache[$agent->id]['new'];
                if (!$agent_perm_old && !$agent_perm_new) {
                    continue;
                }

                $orig_match_real = $new_match_real = null;
                $new_match = $orig_match = false;

                // RESULT_IS_CACHED
                if (!$this->disable_cache && isset($generic_match_cache[$filter->id])) {
                    $orig_match = $generic_match_cache[$filter->id]['orig_match'];
                    $new_match  = $generic_match_cache[$filter->id]['new_match'];

                    if (!$agent_perm_old) {
                        $orig_match = false;
                    }
                    if (!$agent_perm_new) {
                        $new_match = false;
                    }

                    if ($orig_match) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($new_match) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    $scope_cached_counts++;

                // RESULT_NOT_CACHED
                } else {
                    $reset_status = false;
                    if ($filter->sys_name) {
                        // System filters are special in that we ignore status/hold
                        // for notifications
                        $searcher = $filter->getSearcher(array(
                            array('type' => 'status', 'op' => 'ignore'),
                            array('type' => 'hidden_status', 'op' => 'ignore'),
                            array('type' => 'is_hold', 'op' => 'ignore')
                        ));

                        // Reset because we have to re-run to get proper result for add/del lists
                        $reset_status = true;
                    } else {
                        $searcher = $filter->getSearcher();
                    }
                    /** @var \Application\DeskPRO\Searcher\TicketSearch $searcher */
                    $searcher->setPersonContext($agent);

                    $orig_match_failterm = null;
                    $new_match_failterm = null;

                    if ($is_dep_change) {
                        if (!$is_new_ticket && !$agent_perm_old) {
                            $orig_match = false;
                            $orig_match_failterm = 'ticket.department_id';
                        }

                        if (!$agent_perm_new) {
                            $new_match = false;
                            $new_match_failterm = 'ticket.department_id';
                        }
                    }

                    if ($orig_match_failterm === null) {
                        if ($is_new_ticket) {
                            // there is no such thing as an original match with a new ticket
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm);
                        }
                        $orig_match_real = $orig_match;
                    }

                    if ($new_match_failterm === null) {
                        $new_match = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm);
                        $new_match_real = $new_match;
                    }

                    if ($orig_match && $agent_perm_old) {
                        $filter_change->originalMatchForAgent($agent);

                    }
                    if ($new_match && $agent_perm_new) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    if ($reset_status) {
                        $searcher = $filter->getSearcher();
                        $searcher->setPersonContext($agent);

                        if ($is_new_ticket) {
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm);
                        }
                        $new_match = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm);

                        $orig_match_real = $orig_match;
                        $new_match_real  = $new_match;
                    }

                    if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] New match: %s -- Orig match: %s", $new_match ? 'yes' : 'no', $orig_match ? 'yes' : 'no'));
                    if (!$orig_match) {
                        if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] \tOrig failed term: %s", $orig_match_failterm));
                    }
                    if (!$new_match) {
                        if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] \tNew failed term: %s", $new_match_failterm));
                    }

                    if ($new_match_real !== null && $orig_match_real !== null && !isset($generic_match_cache[$filter->id]) && !isset($not_cachable_filters[$filter->id])) {
                        if (!$searcher->needsPersonContext()) {
                            $generic_match_cache[$filter->id] = array(
                                'orig_match' => $orig_match,
                                'new_match' => $new_match,
                            );
                        } else {
                            $not_cachable_filters[$filter->id] = $filter->id;
                        }
                    }
                } // end RESULT_NOT_CACHED

                if (!$orig_match AND !$new_match) {
                    if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both no-match)", $agent->id));
                } elseif ($orig_match AND $new_match) {
                    if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both match)", $agent->id));
                } elseif ($orig_match AND !$new_match) {
                    if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: removed from list", $agent->id));
                    $filter_change->removeForAgent($agent);
                } elseif (!$orig_match AND $new_match) {
                    if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: added to list", $agent->id));
                    $filter_change->addForAgent($agent);
                }

                $scope_counts++;
            }

            if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] DONE FILTER #%d :: %.4fs", $filter->id, microtime(true)-$filter_ts));
        }

        $changed_filters = array();
        foreach ($changed as $fid => $filter_change) {
            if ($filter_change->hasOriginalMatches() || $filter_change->hasNewMatches()) {
                $changed_filters[$fid] = $filter_change;
            }
        }

        $logger->debug(sprintf("[FilterChangeDetector] The following filters could not be optimised: %s", implode(', ', $not_cachable_filters)));

        $logger->info(sprintf("[FilterChangeDetector] Found %d filters in %d iterations (%d of those were cached). Time: %.4fs", count($changed_filters), $scope_counts, $scope_cached_counts, microtime(true)-$time));

        // Add changed filters from previous set
        if ($exist_set) {
            $old_changed_filters = $exist_set->getChangedFilters();
            $copied_ids = array();
            foreach ($checker->getAffectedFiltersWithNoChanges() as $f) {
                if (isset($old_changed_filters[$f->id])) {
                    if (isset($changed_filters[$f->id])) {
                        $old_changed_filters[$f->id]->merge($changed_filters[$f->id]);
                    }
                    $changed_filters[$f->id] = $old_changed_filters[$f->id];
                    $copied_ids[] = $f->id;
                }
            }

            if ($copied_ids) {
                $logger->info(sprintf("[FilterChangeDetector] Found %d additional filters from previous detection set", count($copied_ids)));
            }
        }

        $set = new FilterChangeSet($ticket, $state->getStateVersion(), $checker->getAffectedFilters(), $changed_filters, $checker->getNewestFieldVersions());

        if ($context) {
            $context->getVars()->set('filter_change_set', $set);
        }

        return $set;
    }
}
