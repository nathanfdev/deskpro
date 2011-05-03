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
class AgentAction implements ActionInterface, PersonContextInterface
{
	protected $agent_id;
	protected $person_context;

	public function __construct($agent_id)
	{
		$this->agent_id = $agent_id;
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
		$agent_id = $this->agent_id;

		if ($agent_id == -1) {
			// Invalid context
			if (!$this->person_context OR !$this->person_context['is_agent']) {
				return;
			}

			$agent_id = $this->person_context['id'];
		}

		$ticket['agent_id'] = $this->agent_id;
	}


	/**
	 * Get the agent id
	 * 
	 * @return int
	 */
	public function getAgentId()
	{
		return $this->agent_id;
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