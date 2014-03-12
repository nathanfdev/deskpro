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
	 * @var \Monolog\Logger
	 */
	private $logger;


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
			$agent->loadHelper('AgentTeam');
			$agent->loadHelper('AgentPermissions');
			$agent->loadHelper('PermissionsManager');

			$teams = $agent->getHelper('AgentTeam')->getAgentTeamIds();
			foreach ($teams as $tid) {
				if (!isset($this->team_to_agents[$tid])) {
					$this->team_to_agents[$tid] = array();
				}

				$this->team_to_agents[$tid][] = $agent;
			}
		}

		$this->logger = new NullLogger();
	}


	/**
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * Goes through filters to determine which filters are affected
	 * by the changes.
	 *
	 * @param Ticket $ticket
	 * @return \Application\DeskPRO\Entity\TicketFilter[]|array
	 */
	private function getAffectedFilters(Ticket $ticket)
	{

		$affected_filters = array();
		$this->logger->info(sprintf("[FilterChangeDetector] <Ticket:%d> Checking %d filters", $ticket->id, count($this->filters)));

		$changed_fields = $ticket->getStateChangeRecorder()->getChangedFields();
		$changed_fields = array_combine($changed_fields, $changed_fields);

		$is_hidden_change = false;
		if (isset($changed_fields['hidden_status'])) {
			$is_hidden_change = true;
		}

		$is_new_messages = false;
		if (isset($changed_fields['messages'])) {
			$is_new_messages = true;
		}

		foreach ($this->filters as $f) {
			if ($is_new_messages || $is_hidden_change || $f->getSearcher()->hasAnyAffectedFields($changed_fields)) {
				$affected_filters[] = $f;
			}
		}

		return $affected_filters;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return FilterChangeSet
	 */
	public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context = null)
	{
		$state = $ticket->getStateChangeRecorder();

		// See if theres a cached verson on the context
		if ($context && $context->getVars()->has('filter_change_set')) {
			$set = $context->getVars()->get('filter_change_set');
			if ($set->getTicket()->id == $ticket->id && $set->getStateId() >= $state->getStateVersion()) {
				return $set;
			}
		}

		$affected_filters = $this->getAffectedFilters($ticket);

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

		$this->logger->info(sprintf("Checking %d filters with on %d agents", count($affected_filters), count($this->agents)));

		foreach ($affected_filters as $filter) {
			if ($filter->sys_name == 'archive_deleted') {
				continue;
			}

			$filter_ts = microtime(true);

			$filter_change = new FilterChange($filter);
			$changed[$filter->id] = $filter_change;

			$this->logger->debug(sprintf("----- BEGIN #%d %s -----", $filter->id, $filter->title));

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

				if ($is_dep_change) {
					if (!$is_new_ticket && !$agent->AgentPermissions->isDepartmentAllowed($old_dep_id)) {
						$orig_match = false;
						$orig_match_failterm = 'ticket.department_id';
					}

					if (!$agent->AgentPermissions->isDepartmentAllowed($new_dep_id)) {
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

				if ($orig_match && $agent->PermissionsManager->TicketChecker->canView($orig_ticket)) {
					$filter_change->originalMatchForAgent($agent);

				}
				if ($new_match && $agent->PermissionsManager->TicketChecker->canView($new_ticket)) {
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
					$this->logger->debug(sprintf("Agent scope %d: nochange (both no-match)", $agent->id));
				} else if ($orig_match AND $new_match) {
					$this->logger->debug(sprintf("Agent scope %d: nochange (both match)", $agent->id));
				} else if ($orig_match AND !$new_match) {
					$this->logger->debug(sprintf("Agent scope %d: removed from list", $agent->id));
					$filter_change->removeForAgent($agent);
				} else if (!$orig_match AND $new_match) {
					$this->logger->debug(sprintf("Agent scope %d: added to list", $agent->id));
					$filter_change->addForAgent($agent);
				}

				if (!$orig_match) {
					$this->logger->debug(sprintf("\tOrig failed term: %s", $orig_match_failterm));
				}
				if (!$new_match) {
					$this->logger->debug(sprintf("\tNew failed term: %s", $new_match_failterm));
				}

				$scope_counts++;
			}

			$this->logger->debug(sprintf("DONE FILTER #%d :: %.4fs", $filter->id, microtime(true)-$filter_ts));
		}

		$changed_filters = array();
		foreach ($changed as $fid => $filter_change) {
			if ($filter_change->hasOriginalMatches() || $filter_change->hasNewMatches()) {
				$changed_filters[$fid] = $filter_change;
			}
		}

		$this->logger->info(sprintf("Found %d filters in %d iterations taking %.4fs", count($changed_filters), $scope_counts, microtime(true)-$time));

		$set = new FilterChangeSet($ticket, $state->getStateVersion(), $affected_filters, $changed_filters);

		if ($context) {
			$context->getVars()->set('filter_change_set', $set);
		}

		return $set;
	}
}