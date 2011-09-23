<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class TicketEdit
{
	/**
	 * Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	/**
	 * Apply a standard actions array to this ticket.
	 *
	 * @param array $actions
	 */
	public function applyActions(array $actions)
	{
		$return = array();

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
					$this->ticket['department_id'] = $action;
					break;

				case 'category_id':
					$this->ticket['category_id'] = $action;
					break;

				case 'agent':
				case 'agent_id':
					$this->ticket['agent_id'] = $action;
					break;

				case 'agent_team':
				case 'agent_team_id':
					$this->ticket['agent_team_id'] = $action;
					break;

				case 'product_id':
					$this->ticket['product_id'] = $action;
					break;

				case 'priority_id':
					$this->ticket['priority_id'] = $action;
					break;

				case 'workflow_id':
					$this->ticket['workflow_id'] = $action;
					break;

				case 'status':
					$this->ticket['status'] = $action;
					break;

				case 'flag':
					$agent = App::getCurrentPerson();

					if (!$agent) {
						continue;
						//todo err?
					}

					$this->ticket->setFlagForPerson($agent, $action);
					break;

				case 'add_labels':
					foreach ((array)$action as $label) {
						$this->ticket->getLabelManager()->addLabel($label);
					}
					break;

				case 'remove_labels':
					foreach ((array)$action as $label) {
						$this->ticket->getLabelManager()->removeLabel($label);
					}
					break;

				case 'add_participant':

					$person = App::getEntityRepository('DeskPRO:Perosn')->find($action['add_participant']);

					if (!$person) {
						continue;
					}

					$this->ticket->addParticipant($person);

					break;

				case 'new_reply':
					$agent = App::getCurrentPerson();

					if (!$agent) {
						continue;
						//todo err?
					}

					$message = new Entity\TicketMessage();
					$message['person']  = $agent;
					$message['ticket']  = $this->ticket;
					$message['message'] = $action['new_reply'];

					$this->ticket->addMessage($message);

					$return['new_reply'] = $message;

					break;

				case 'ticket_field':

					$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
					foreach ($field->getHandler()->getDataFromForm($action['value']) as $info) {
						$this->ticket->setCustomData($info[0], $info[1], $info[2]);
					}

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
