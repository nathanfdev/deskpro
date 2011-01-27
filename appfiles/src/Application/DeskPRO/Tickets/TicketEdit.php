<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class TicketEdit
{
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

		foreach ($actions as $type => $action) {
			switch ($type) {
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

				case 'product_id':
					$this->ticket['product_id'] = $action;
					break;

				case 'priority_id':
					$this->ticket['priority_id'] = $action;
					break;

				case 'status':
					$this->ticket['status'] = $action;
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
					$message['message'] = $action['message'];

					$this->ticket->addMessage($message);
					
					$return['new_reply'] = $message;

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
		foreach ($ticket_field_datas as $field_id => $value) {
			$this->setCustomData($field_id, $value);
		}
	}

	public function setCustomData($field_id, $value)
	{
		$custom_data = $this->ticket->getCustomDataForField($field_id);
		if (!$custom_data) {
			if ($value === null) return null;
			$field = App::getApi('custom_fields.tickets')->getFieldFromId($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = $field->createNewDataObject();
		}

		if ($value === null) {
			$this->ticket['custom_data']->removeElement($custom_data);
			App::getOrm()->remove($custom_data);
			return null;
		}

		$custom_data->setData($value);
		$this->ticket->addCustomData($custom_data);

		return $custom_data;
	}

	public function save()
	{
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();
	}
}