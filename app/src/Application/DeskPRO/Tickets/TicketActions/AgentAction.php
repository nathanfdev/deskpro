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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

/**
 * Sets agent
 */
class AgentAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
	protected $agent_id;
	protected $person_context;

	public function __construct($agent)
	{
		$this->agent_id = $agent;
	}


	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	public function checkPermission(Ticket $ticket, Person $person)
	{
		if ($ticket->getAgentId() == $this->agent_id) {
			return true;
		}

		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
			if ($this->agent_id == $person->getId() && $person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
				return true;
			}
			return false;
		}

		return true;
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

		$ticket['agent_id'] = $agent_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		$agent_id = $this->agent_id;

		if ($agent_id == -1) {
			// Invalid context
			if (!$this->person_context OR !$this->person_context['is_agent']) {
				return array();
			}

			$agent_id = $this->person_context['id'];
		}

		if ($ticket['agent_id'] == $agent_id) {
			return array();
		}

		return array(
			array('action' => 'agent', 'agent_id' => $agent_id)
		);
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


	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
        $tr = App::getTranslator();

		if ($this->agent_id == -1) {
			if ($this->trigger) {
				switch ($this->trigger->event_trigger) {
					case 'new.email.agent': return 'Assign the agent who forwarded the email';
					case 'new.web.agent.portal': return 'Assign the agent who created the ticket';
					case 'update.agent': return 'Assign the agent who made the change';
				}
			}
			return $tr->phrase('agent.tickets.assign_current_action');
		} elseif ($this->agent_id == 0) {
			if ($this->trigger && $this->trigger->event_trigger == 'new.email.agent') {
				return 'Do not assign ticket to anyone';
			}
			return $tr->phrase('agent.tickets.unassign_action');
		} else {
			$name = App::getEntityRepository('DeskPRO:Person')->getAgentNames(array($this->agent_id));
			if (!isset($name[$this->agent_id])) return '';

			return $tr->phrase('agent.tickets.assign_to_agent_action', array('agent' => $name[$this->agent_id]));
		}
	}
}
