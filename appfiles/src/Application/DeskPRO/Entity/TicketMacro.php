<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Ticket macros
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketMacro")
 * @orm:Table(name="ticket_macros")
 */
class TicketMacro extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="labels", type="string", length=1000)
	 */
	protected $labels = '';

	/**
	 * @var bool
	 * @orm:Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @orm:Column(name="actions", type="array")
	 */
	protected $actions;



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



	/**
	 * Get actions for a collection of tickets.
	 *
	 * @param array $tickets
	 */
	public function getActionsArrayForCollection($tickets = null)
	{
		$actions = array();

		if ($tickets) {
			foreach ($tickets as $ticket) {
				$actions[$ticket['id']] = $this->getActionsArray($ticket);
			}
		}

		return $actions;
	}



	public function performOnTicket(Ticket $ticket)
	{
		foreach ($this->actions as $action) {
			switch ($action['rule_type']) {
				case 'department':
					$ticket['department_id'] = $action['department'];
					break;

				case 'category':
					$ticket['category_id'] = $action['category'];
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
					$ticket['agent_id'] = $action['agent'];
					break;

				case 'product':
					$ticket['product_id'] = $action['product'];
					break;

				case 'priority':
					$ticket['priority_id'] = $action['priority'];
					break;

				case 'reply':
					$agent = App::getCurrentPerson();

					if (!$agent) {
						return;
						//todo err?
					}

					$message = new Entity\TicketMessage();
					$message['person']  = $agent;
					$message['ticket']  = $ticket;
					$message['message'] = $action['reply'];

					$ticket->addMessage($message);

					App::getOrm()->persist($message);

					break;
			}
		}

		App::getOrm()->persist($ticket);
	}
}