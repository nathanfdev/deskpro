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

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

/**
 * Sets agent
 */
class AgentTeamAction implements ActionInterface, PersonContextInterface
{
	protected $agent_team_id;
	protected $person_context;

	public function __construct($agent_team_id)
	{
		$this->agent_team_id = $agent_team_id;
	}

	
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$agent_team_id = $this->agent_team_id;

		if ($agent_team_id == -1) {
			// Invalid context
			if (!$this->person_context OR !$this->person_context['is_agent']) {
				return;
			}

			$this->person_context->loadHelper('AgentTeam');
			$agent_team_id = $this->person_context->getHelper('AgentTeam')->getPrimaryTeamId();

			// Invalid agent team (eg. agent has no teams)
			if (!$agent_team_id) {
				return;
			}
		}

		$ticket['agent_team_id'] = $agent_team_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		$agent_team_id = $this->agent_team_id;

		if ($agent_team_id == -1) {
			// Invalid context
			if (!$this->person_context OR !$this->person_context['is_agent']) {
				return array();
			}

			$this->person_context->loadHelper('AgentTeam');
			$agent_team_id = $this->person_context->getHelper('AgentTeam')->getPrimaryTeamId();

			// Invalid agent team (eg. agent has no teams)
			if (!$agent_team_id) {
				return array();
			}
		}
		
		if ($ticket['agent_team_id'] == $agent_team_id) {
			return array();
		}

		return array(
			array('action' => 'agent_team', 'agent_id' => $agent_team_id)
		);
	}


	/**
	 * Get the agent team id
	 *
	 * @return int
	 */
	public function getAgentTeamId()
	{
		return $this->agent_team_id;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}
}