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

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

class FilterChangeDetector
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var \Application\DeskPRO\Tickets\ExecutorContext
	 */
	private $context;

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
	 * @var \Application\DeskPRO\Entity\TicketFilter[]
	 */
	private $affected_filters;

	/**
	 * @var \Application\DeskPRO\Entity\TicketFilter[]
	 */
	private $changed_filters;


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param \Application\DeskPRO\Entity\TicketFilter[] $filters
	 * @param \Application\DeskPRO\Entity\Person[] $agents
	 */
	public function __construct(Ticket $ticket, ExecutorContext $context, array $filters, array $agents)
	{
		$this->ticket  = $ticket;
		$this->context = $context;
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
	}


	/**
	 * Goes through filters to determine which filters are affected
	 * by the changes.
	 *
	 * @return \Application\DeskPRO\Entity\TicketFilter[]|array
	 */
	public function getAffectedFilters()
	{
		if ($this->affected_filters !== null) {
			return $this->affected_filters;
		}

		$this->affected_filters = array();
		$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Checking %d filters", count($this->filters)));

		$changed_fields = $this->ticket->getStateChangeRecorder()->getChangedFields();
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
				$this->affected_filters[] = $f;
			}
		}

		return $this->affected_filters;
	}


	/**
	 * Returns an array of filters that were affected by the change.
	 *
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	public function getUpdatedFilters()
	{
		if ($this->changed_filters !== null) {
			return $this->changed_filters;
		}

		$this->changed_filters = array();
		$state = $this->ticket->getStateChangeRecorder();

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
			if ($this->ticket->department) {
				$new_dep_id = $this->ticket->department->id;
			}
		}

		$orig_ticket = $this->ticket->getOriginalStateClone();
		$new_ticket  = $this->ticket;

		$scope_counts = 0;
		$time = microtime(true);

		$changed = array();

		$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Checking %d filters with on %d agents", count($this->getAffectedFilters()), count($this->agents)));

		foreach ($this->getAffectedFilters() as $filter) {
			if ($filter->sys_name == 'archive_deleted') {
				continue;
			}

			$filter_ts = microtime(true);

			$changed[$filter->id] = array(
				'add'        => array(),
				'del'        => array(),
				'orig_match' => array(),
				'new_match'  => array(),
				'filter'     => $filter
			);

			$this->context->getLogger()->info(sprintf("[FilterChangeDetector] ----- BEGIN #%d %s -----", $filter->id, $filter->title));

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
					$changed[$filter->id]['orig_match'][] = $agent;
				}
				if ($new_match && $agent->PermissionsManager->TicketChecker->canView($new_ticket)) {
					$changed[$filter->id]['new_match'][] = $agent;
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
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both no-match)", $agent->id));
				} else if ($orig_match AND $new_match) {
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Agent scope %d: nochange (both match)", $agent->id));
				} else if ($orig_match AND !$new_match) {
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Agent scope %d: removed from list", $agent->id));
					$changed[$filter->id]['del'][] = $agent;
				} else if (!$orig_match AND $new_match) {
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Agent scope %d: added to list", $agent->id));
					$changed[$filter->id]['add'][] = $agent;
				}

				if (!$orig_match) {
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] \tOrig failed term: %s", $orig_match_failterm));
				}
				if (!$new_match) {
					$this->context->getLogger()->info(sprintf("[FilterChangeDetector] \tNew failed term: %s", $new_match_failterm));
				}

				$scope_counts++;
			}

			$this->context->getLogger()->info(sprintf("[FilterChangeDetector] DONE FILTER #%d :: %.4fs", $filter->id, microtime(true)-$filter_ts));
		}

		$this->changed_filters = array();
		foreach ($changed as $fid => $changes) {
			if ($changes['orig_match'] || $changes['new_match']) {
				$this->changed_filters[$fid] = $changes;
			}
		}

		$this->context->getLogger()->info(sprintf("[FilterChangeDetector] Found %d filters in %d iterations taking %.4fs", count($this->changed_filters), $scope_counts, microtime(true)-$time));

		return $this->changed_filters;
	}


	/**
	 * Get an array of client messages to send to clients about lists updating.
	 *
	 * @return \Application\DeskPRO\Entity\ClientMessage[]
	 */
	public function getListUpdateClientMessages()
	{
		$messages = array();

		#------------------------------
		# CMs for filters
		#------------------------------

		foreach ($this->getUpdatedFilters() as $change_info) {
			$filter = $change_info['filter'];

			foreach ($change_info['add'] as $agent) {
				$cm = new ClientMessage();
				$cm->channel = 'agent.filter-update';
				$cm->data = array(
					'ticket_id' => $this->ticket->id,
					'filter_id' => $filter->id,
					'op'        => 'add',
				);
				$cm->for_person = $agent;
				$cm->created_by_client = 'sys';
				$messages[] = $cm;
			}
			foreach ($change_info['del'] as $agent) {
				$cm = new ClientMessage();
				$cm->channel = 'agent.filter-update';
				$cm->data = array(
					'ticket_id' => $this->ticket->id,
					'filter_id' => $filter->id,
					'op'        => 'del',
				);
				$cm->for_person = $agent;
				$cm->created_by_client = 'sys';
				$messages[] = $cm;
			}
		}

		$this->context->getLogger()->info(sprintf("[FilterChangeDetector] %d client message signals", count($messages)));

		return $messages;
	}
}