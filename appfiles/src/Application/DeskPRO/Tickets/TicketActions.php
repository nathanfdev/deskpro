<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use Orb\Util\Arrays;

class TicketActions
{
	protected $actions = array();

	public function __construct(array $actions)
	{
		$this->actions = $actions;
	}

	/**
	 * Get a simple array of actions used to pass back to views to update
	 * UI.
	 *
	 * $ticket may be null, in which case no conditions are assumed.
	 *
	 * @param Entity\Ticket $ticket The context (used ex in replies for replacements)
	 */
	public function getActionsArray(Entity\Ticket $ticket = null)
	{
		$actions = array();
		$preview = array();

		foreach ($this->actions as $action) {

			$term = $action['rule_type'];
			$term_id = null;

			// $term of ticket_field[12] becomes $term=ticket_field, $term_id=12
			$m = null;
			if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
				$term = $m[1];
				$term_id = $m[2];
			}

			switch ($term) {
				case 'department':
					if (!$ticket OR $ticket['department_id'] != $action['department']) {
						$actions['department_id'] = $action['department'];
					}
					break;

				case 'category':
					if (!$ticket OR $ticket['category_id'] != $action['category']) {
						$actions['category_id'] = $action['category'];
					}
					break;

				case 'agent':

					// -1 means "current user" -- used for generic shared macros
					if ($action['agent'] == -1) {
						$agent = App::getCurrentPerson();
						if ($agent) {
							$action['agent'] = $agent['id'];
						} else {
							return;// todo err?
						}
					}

					if (!$ticket OR $ticket['agent_id'] != $action['agent']) {
						$actions['agent_id'] = $action['agent'];
					}
					break;

				case 'agent_team':

					if (!$ticket OR $ticket['agent_team_id'] != $action['agent_team']) {
						$actions['agent_team_id'] = $action['agent_team'];
					}

					break;

				case 'product':
					if (!$ticket OR $ticket['product_id'] != $action['product']) {
						$actions['product_id'] = $action['product'];
					}
					break;

				case 'priority':
					if (!$ticket OR $ticket['priority_id'] != $action['priority']) {
						$actions['priority_id'] = $action['priority'];
					}
					break;

				case 'reply':
					$actions['new_reply'] = $action['new_reply'];
					$agent = App::getCurrentPerson();
					break;

				case 'status':
					$actions['status'] = $action['status'];
					break;

				case 'flag':
					$actions['flag'] = $action['flag'];
					$agent = App::getCurrentPerson();
					break;

				case 'add_labels':
					$actions['add_labels'] = explode(',', $action['labels']);
					array_walk($actions['add_labels'], 'trim');
					$actions['add_labels'] = Arrays::removeFalsey($actions['add_labels']);
					if (!$actions['add_labels']) {
						unset($actions['add_labels']);
					}
					break;

				case 'remove_labels':
					$actions['remove_labels'] = explode(',', $action['labels']);
					array_walk($actions['remove_labels'], 'trim');
					$actions['remove_labels'] = Arrays::removeFalsey($actions['remove_labels']);
					if (!$actions['remove_labels']) {
						unset($actions['remove_labels']);
					}
					break;

				case 'add_participant':
					$actions['add_participant'] = $action['add_participant'];
					break;

				case 'ticket_field':

					$value = $action;
					unset($value['rule_type'], $value['op'], $value['renderable_value']);

					$field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
					if (!$field) {
						break;
					}

					$act = array(
						'type' => 'ticket_field',
						'field_id' => $term_id,
						'value' => $value,
						'value_display' => $field->getHandler()->renderHtml($action['renderable_value'])
					);

					$actions[$action['rule_type']] = $act;
					break;
			}
		}

		return $actions;
	}
}