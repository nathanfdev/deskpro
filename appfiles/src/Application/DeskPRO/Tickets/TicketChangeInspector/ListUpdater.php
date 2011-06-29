<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Ticket;
use \Application\DeskPRO\Entity\TicketFilter;
use \Application\DeskPRO\Entity\ClientMessage;

use \Application\DeskPRO\Tickets\TicketChangeTracker;

class ListUpdater
{
	/**
	 * Runs ticket through filters to determine actual
	 * affected filters
	 * (Filters can be specifically updated)
	 */
	const MODE_CHECK = 'check';

	/**
	 * Just runs through filters affected fields to see
	 * which filters *might* be affected, and returns those.
	 * (In this case, the filters would just refresh, possibly ones that didnt need to)
	 */
	const MODE_SHALLOW = 'shallow';

	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketListener
	 */
	protected $tracker;

	/**
	 * @var array
	 */
	protected $changed_fields;

	/**
	 * @var array
	 */
	protected $active_agents = array();

	protected $mode;

	public function __construct(TicketChangeTracker $tracker, $mode = self::MODE_SHALLOW)
	{
		$this->tracker = $tracker;
		$this->mode = $mode;

		$this->active_agents = App::getEntityRepository('DeskPRO:Person')->getActiveAgents();
		$this->agent_to_teams   = App::getEntityRepository('DeskPRO:AgentTeam')->getTeamIdsForAgents();
		$this->teams_to_agents  = array();

		foreach ($this->agent_to_teams as $agent_id => $team_ids) {
			foreach ($team_ids as $team_id) {
				if (!isset($this->teams_to_agents[$team_id])) {
					$this->teams_to_agents[$team_id] = array();
				}

				$this->teams_to_agents[$team_id][] = $agent_id;
			}
		}

		$changed_fields = array();

		foreach ($tracker->getAllChangedProperties() as $prop => $info) {
			switch ($prop) {
				case 'agent':
					$changed_fields[] = 'ticket.agent_id';
					break;

				case 'category':
					$changed_fields[] = 'ticket.category_id';
					break;

				case 'department':
					$changed_fields[] = 'ticket.department_id';
					break;

				case 'priority':
					$changed_fields[] = 'ticket.priority_id';
					break;

				case 'product':
					$changed_fields[] = 'ticket.product_id';
					break;

				case 'status':
					$changed_fields[] = 'ticket.status';
					break;

				case 'hidden_status':
					$changed_fields[] = 'ticket.hidden_status';
					break;
			}
		}

		$this->changed_fields = $changed_fields;
	}

	/**
	 * Check terms in a filter to see if this change could have affected it
	 * 
	 * @param \Application\DeskPRO\Entity\TicketFilter $filter
	 * @return bool
	 */
	public function checkTerms(TicketFilter $filter)
	{
		$searcher = $filter->getSearcher();
		$affected_fields = $searcher->getAffectedFields();
		
		foreach ($this->changed_fields as $f) {
			if (in_array($f, $affected_fields)) {
				return true;
			}
		}

		return false;
	}

	public function getUpdateMessages(TicketFilter $filter)
	{
		#------------------------------
		# Filters have to be run from the scope
		# of a particular agent, so figure out
		# which agents the filter affects
		#------------------------------

		$scopes = null;

		if ($filter['is_global']) {
			$scopes = $this->active_agents;
		} else if ($filter->agent_team) {
			$team_id = $filter->agent_team['id'];
			if (!isset($this->teams_to_agents[$team_id])) {
				return array();
			}

			$scopes = array();
			foreach ($this->teams_to_agents[$team_id] as $agent_id) {
				$scopes[] = $this->active_agents[$agent_id];
			}
		} else if ($filter->person) {
			$person_id = $filter->person['id'];
			if (!isset($this->active_agents[$person_id])) {
				return array();
			}

			$scopes = array($filter->person);
		}

		if (!$scopes) return array();

		#------------------------------
		# Run the PHP-based check for each scope
		#------------------------------

		$client_messages = array();

		foreach ($scopes as $agent) {
			$searcher = $filter->getSearcher();
			$searcher->setPerson($agent);

			$orig_match = $searcher->doesTicketMatch($this->tracker->getOriginalTicket());
			$new_match  = $searcher->doesTicketMatch($this->tracker->getTicket());

			if (!$orig_match AND !$new_match) {
				// Nothing changed
			} else if ($orig_match AND $new_match) {
				// Nothing changed again
			} else if ($orig_match AND !$new_match) {
				// Remove from lists
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'agent.filter-update',
					'data' => array(
						'ticket_id'  => $this->tracker->getTicket()->getId(),
						'filter_id'  => $filter['id'],
						'for_person' => $agent,
						'op' => 'del'
					),
					'created_by_client' => 'sys'
				));
				$client_messages[] = $cm;
			} else if (!$orig_match AND $new_match) {
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'agent.filter-update',
					'data' => array(
						'ticket_id'  => $this->tracker->getTicket()->getId(),
						'filter_id'  => $filter['id'],
						'for_person' => $agent,
						'op' => 'add'
					),
					'created_by_client' => 'sys'
				));
			}
		}

		return $client_messages;
	}

	public function done()
	{
		#------------------------------
		# Run through filters to see which apply to the change
		#------------------------------

		$filters = App::getEntityRepository('DeskPRO:TicketFilter')->getAllForAgents($this->active_agents);
		$filters_apply = array();

		foreach ($filters as $filter) {
			if ($this->checkTerms($filter)) {
				$filters_apply[] = $filter;
			}
		}

		unset($filters);

		if (!$filters_apply) return;

		// If we're in shallow mode, can just tell clients to update
		// these found filters now
		if ($this->mode == self::MODE_SHALLOW) {
			$client_messages = array();
			foreach ($filters_apply as $filter) {
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'agent.filter-update',
					'data' => array(
						'ticket_id'  => $this->tracker->getTicket()->getId(),
						'filter_id'  => $filter['id'],
						'op' => 'refresh'
					),
					'created_by_client' => 'sys'
				));
			}

			App::getOrm()->transactional(function ($em) use ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}

				$em->flush();
			});

			return;
		}

		#------------------------------
		# Now run through each
		#------------------------------

		// Check mode we'll actually run the PHP-based logic checks
		// to see which filters have the ticket and need it to be
		// added/removed

		$client_messages = array();

		foreach ($filters_apply as $filter) {
			$client_messages = array_merge($client_messages, $this->getUpdateMessages($filter));
		}

		if ($client_messages) {
			App::getOrm()->transactional(function ($em) use ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}

				$em->flush();
			});
		}
	}
}