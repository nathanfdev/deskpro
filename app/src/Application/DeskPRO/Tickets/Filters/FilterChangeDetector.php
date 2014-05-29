<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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


use Application\DeskPRO\Entity\Ticket;
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
	 * @param \Application\DeskPRO\Entity\TicketFilter[] $filters
	 * @param \Application\DeskPRO\Entity\Person[] $agents
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
	}


	/**
	 * Goes through filters to determine which filters are affected
	 * by the changes.
	 *
	 * @param Ticket $ticket
	 * @param Logger $logger
	 * @return \Application\DeskPRO\Entity\TicketFilter[]|array
	 */
	private function getAffectedFilters(Ticket $ticket, Logger $logger)
	{

		if (!$logger) {
			$logger = new NullLogger();
		}

		$affected_filters = array();
		$logger->info(sprintf("[FilterChangeDetector] <Ticket:%d> Checking %d filters", $ticket->id, count($this->filters)));

		$changed_fields = $ticket->getStateChangeRecorder()->getChangedFields();
		$logger->debug(sprintf("[FilterChangeDetector] <Ticket:%d> Changed fields: %s", $ticket->id, implode(', ', $changed_fields)));

		// Convert the detected changed fields into names
		// the searcher defines
		$changed_fields = array_map(function($field_name) {
			switch ($field_name) {
				case 'language': return 'ticket.language_id';
				case 'agent': return 'ticket.agent_id';
				case 'department': return 'ticket.department_id';
				case 'category': return 'ticket.category_id';
				case 'priority': return 'ticket.priority_id';
				case 'workflow': return 'ticket.workflow_id';
				case 'product': return 'ticket.product_id';
				case 'person': return 'ticket.person_id';
				case 'agent_team': return 'ticket.agent_team_id';
				case 'organization': return 'ticket.organization_id';
				default: return "ticket.$field_name";
			}
		}, $changed_fields);

		if ($changed_fields) {
			$changed_fields = array_combine($changed_fields, $changed_fields);
		}

		$is_hidden_change = false;
		if (isset($changed_fields['ticket.hidden_status'])) {
			$is_hidden_change = true;
		}

		$is_new_messages = false;
		if (isset($changed_fields['ticket.message'])) {
			$is_new_messages = true;
		}

		foreach ($this->filters as $f) {
			if ($is_new_messages || $is_hidden_change || $f->getSearcher()->hasAnyAffectedFields($changed_fields)) {
				$affected_filters[] = $f;
			}
		}

		$logger->info(sprintf("[FilterChangeDetector] <Ticket:%d> %d filters with affected fields", $ticket->id, count($affected_filters)));

		return $affected_filters;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return FilterChangeSet
	 */
	public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context = null)
	{
		$logger = $context->getLogger();
		$state = $ticket->getStateChangeRecorder();

		// See if theres a cached verson on the context
		if ($context && $context->getVars()->has('filter_change_set')) {
			$set = $context->getVars()->get('filter_change_set');
			if ($set->getTicket()->id == $ticket->id && $set->getStateId() >= $state->getStateVersion()) {
				return $set;
			}
		}

		$affected_filters = $this->getAffectedFilters($ticket, $logger);

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
		$time = microtime(true);

		/** @var FilterChange[] $changed */
		$changed = array();

		$logger->info(sprintf("[FilterChangeDetector] Checking %d filters with on %d agents", count($affected_filters), count($this->agents)));

		foreach ($affected_filters as $filter) {
			if ($filter->sys_name == 'archive_deleted') {
				continue;
			}

			$filter_ts = microtime(true);

			$filter_change = new FilterChange($filter);
			$changed[$filter->id] = $filter_change;

			if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] ----- BEGIN #%d %s -----", $filter->id, $filter->title));

			$agent_scopes = array();
			if ($filter->is_global) {
				$agent_scopes = $this->agents;
			} else if ($filter->agent_team) {
				$team_id = $filter->agent_team->id;
				if (isset($this->team_to_agents[$team_id])) {
					foreach ($this->team_to_agents[$team_id] as $agent) {
						$agent_scopes[] = $agent;
					}
				}
			} else if ($filter->person) {
				$agent_scopes[] = $filter->person;
			}

			if (!$agent_scopes) {
				continue;
			}

			foreach ($agent_scopes as $agent) {

				// A filter could belong to an agent that isn't an agent anymore
				if (!$agent->is_agent) {
					continue;
				}

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
				$searcher->setPersonContext($agent);

				$orig_match_failterm = null;
				$new_match_failterm = null;

				// testing check
				// there is no mock for the PermissionsManager yet
				if (!defined('DP_BOOT_MODE') || DP_BOOT_MODE != 'testing') {
					if ($is_new_ticket && !$agent->PermissionsManager->TicketChecker->canView($ticket)) {
						continue;
					}
				}

				if ($is_dep_change) {
					if (!$is_new_ticket && ($agent->isHelperLoader('AgentPermissions') && !$agent->AgentPermissions->isDepartmentAllowed($old_dep_id))) {
						$orig_match = false;
						$orig_match_failterm = 'ticket.department_id';
					}

					if ($agent->isHelperLoader('AgentPermissions') && !$agent->AgentPermissions->isDepartmentAllowed($new_dep_id)) {
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
				}

				if ($new_match_failterm === null) {
					$new_match  = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm);
				}

				if ($orig_match && (!$agent->isHelperLoader('PermissionsManager') || $agent->PermissionsManager->TicketChecker->canView($orig_ticket))) {
					$filter_change->originalMatchForAgent($agent);

				}
				if ($new_match && (!$agent->isHelperLoader('PermissionsManager') || $agent->PermissionsManager->TicketChecker->canView($new_ticket))) {
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
					$new_match  = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm);
				}

				if (!$orig_match AND !$new_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both no-match)", $agent->id));
				} else if ($orig_match AND $new_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both match)", $agent->id));
				} else if ($orig_match AND !$new_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: removed from list", $agent->id));
					$filter_change->removeForAgent($agent);
				} else if (!$orig_match AND $new_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] Agent scope %d: added to list", $agent->id));
					$filter_change->addForAgent($agent);
				}

				if (!$orig_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] \tOrig failed term: %s", $orig_match_failterm));
				}
				if (!$new_match) {
					if ($this->extended_log_info) $logger->debug(sprintf("[FilterChangeDetector] \tNew failed term: %s", $new_match_failterm));
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

		$logger->info(sprintf("[FilterChangeDetector] Found %d filters in %d iterations taking %.4fs", count($changed_filters), $scope_counts, microtime(true)-$time));

		$set = new FilterChangeSet($ticket, $state->getStateVersion(), $affected_filters, $changed_filters);

		if ($context) {
			$context->getVars()->set('filter_change_set', $set);
		}

		return $set;
	}
}