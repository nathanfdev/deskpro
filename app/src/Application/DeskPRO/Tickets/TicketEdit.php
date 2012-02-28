<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Person;

class TicketEdit implements PersonContextInterface
{
	/**
	 * Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	/**
	 * Apply a standard actions array to this ticket.
	 *
	 * @param array $actions
	 */
	public function applyActions(array $actions)
	{
		$return = array();

		if ($this->person_context) {
			$tcheck = $this->person_context->PermissionsManager->TicketChecker;
		}
		$tcheck = null;

		foreach ($actions as $term => $action) {

			$term_id = null;

			// $term of ticket_field[12] becomes $term=ticket_field, $term_id=12
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			switch ($term) {
				case 'department_id':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'department')) {
							break;
						}
					}
					$this->ticket['department_id'] = $action;
					break;

				case 'category_id':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$this->ticket['category_id'] = $action;
					break;

				case 'agent':
				case 'agent_id':

					if ($this->person_context) {
						$agent = $this->in->checkIsset('agent');
						if ($agent == $this->person_context->id && !$tcheck->canModify($ticket, 'assign_self')) {
							$agent = null;
						} elseif (!$tcheck->canModify($ticket, 'assign_agent')) {
							$agent = null;
						}

						if (!$agent) {
							break;
						}
					}

					$this->ticket['agent_id'] = $action;
					break;

				case 'agent_team':
				case 'agent_team_id':

					if ($this->person_context) {
						$team = $this->in->checkIsset('agent_team');
						if ($this->person_context->Agent->isTeamMember($team) && !$tcheck->canModify($ticket, 'assign_self')) {
							$team = null;
						} elseif (!$tcheck->canModify($ticket, 'assign_team')) {
							$team = null;
						}

						if (!$team) {
							break;
						}
					}

					$this->ticket['agent_team_id'] = $action;
					break;

				case 'product_id':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$this->ticket['product_id'] = $action;
					break;

				case 'priority_id':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$this->ticket['priority_id'] = $action;
					break;

				case 'workflow_id':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$this->ticket['workflow_id'] = $action;
					break;

				case 'status':
					if ($this->person_context) {
						$status = $this->in->checkIsset('status');
						if ($status == 'resolved' && !$tcheck->canModify($ticket, 'set_resolved')) {
							$status = null;
						}
						if ($status == 'awaiting_agent' && !$tcheck->canModify($ticket, 'set_awaiting_agent')) {
							$status = null;
						}
						if ($status == 'awaiting_user' && !$tcheck->canModify($ticket, 'set_awaiting_user')) {
							$status = null;
						}
						if (!$status) {
							break;
						}
					}
					$this->ticket['status'] = $action;
					break;

				case 'is_hold':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'set_hold')) {
							break;
						}
					}
					$this->ticket['is_hold'] = $action;
					break;

				case 'flag':
					$agent = App::getCurrentPerson();

					if (!$agent) {
						continue;
					}

					$this->ticket->setFlagForPerson($agent, $action);
					break;

				case 'add_labels':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'labels')) {
							break;
						}
					}
					foreach ((array)$action as $label) {
						$this->ticket->getLabelManager()->addLabel($label);
					}
					break;

				case 'remove_labels':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'labels')) {
							break;
						}
					}
					foreach ((array)$action as $label) {
						$this->ticket->getLabelManager()->removeLabel($label);
					}
					break;

				case 'add_participant':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'cc')) {
							break;
						}
					}

					$person = App::getEntityRepository('DeskPRO:Perosn')->find($action['add_participant']);

					if (!$person) {
						continue;
					}

					$this->ticket->addParticipant($person);

					break;

				case 'new_reply':
					if ($this->person_context) {
						if (!$tcheck->canReply($this->ticket)) {
							break;
						}
					}
					$agent = App::getCurrentPerson();

					if (!$agent) {
						continue;
					}

					$message = new Entity\TicketMessage();
					$message['person']  = $agent;
					$message['ticket']  = $this->ticket;
					$message['message'] = $action['new_reply'];

					$this->ticket->addMessage($message);

					$return['new_reply'] = $message;

					break;

				case 'ticket_field':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
					foreach ($field->getHandler()->getDataFromForm($action['value']) as $info) {
						$this->ticket->setCustomData($info[0], $info[1], $info[2]);
					}

					break;

				case 'urgency':
					if ($this->person_context) {
						if (!$tcheck->canModify($this->ticket, 'fields')) {
							break;
						}
					}
					$this->ticket->urgency = $action;
					break;
			}
		}

		return $return;
	}

	public function addMessage(Entity\TicketMessage $message)
	{
		$this->ticket->addMessage($message);
	}

	public function setCustomDataAll(array $ticket_field_datas)
	{
		foreach ($ticket_field_datas as $info) {
			$this->ticket->setCustomData($info[0], $info[1], $info[2]);
		}
	}

	public function save()
	{
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();
	}
}
