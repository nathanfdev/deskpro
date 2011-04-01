<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketLog;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

/**
 * The ticket logger takes care of logging changes done to a ticket.
 */
class Logger implements \Doctrine\Common\PropertyChangedListener
{
	/**
	 * When true, performing ticket actions which shouldnt cause additional triggers to run again
	 * @var bool
	 */
	protected $is_performing = false;
	protected $ticket;
	public $entered_logs = array();
	protected $events = array();

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		// If the ticket isnt created yet, then we dont care
		// about property changes, because they havent really changed have they?
		if (!$this->ticket['id']) {
			return;
		}
		
		$this->logChange($prop, $old_val, $new_val);
	}

	protected function _addEventType($event_type)
	{
		Arrays::pushUnique($this->events, $event_type);
	}

	public function logChange($prop, $old_val, $new_val)
	{
		$action = null;

		switch ($prop) {
			case 'agent':
				$action = new Actions\Agent($old_val, $new_val);
				break;

			case 'category':
				$action = new Actions\Category($old_val, $new_val);
				break;

			case 'department':
				$action = new Actions\Department($old_val, $new_val);
				break;

			case 'messages':
				if ($new_val) {
					$action = new Actions\Message($new_val);
				} else {
					// $old_val means removed
				}
				break;

			case 'priority':
				$action = new Actions\Priority($old_val, $new_val);
				break;

			case 'product':
				$action = new Actions\Product($old_val, $new_val);
				break;

			case 'status':
				$action = new Actions\Status($old_val, $new_val);
				break;

			case 'hidden_status':
				$action = new Actions\Status($old_val, $new_val);
				break;
		}

		if ($action) {
			$name = $action->getLogName();
			$this->entered_logs[$name] = $action;
		}
	}

	public function logAction(Actions\LogActionInterface $action)
	{
		$name = $action->getLogName();
		$this->entered_logs[$name] = $action;
	}

	public function done()
	{
		if (!$this->entered_logs) {
			return;
		}

		App::getOrm()->beginTransaction();

		foreach ($this->entered_logs as $name => $action) {

			$this->_addEventType($action->getEventType());

			$ticket_log = new Entity\TicketLog();
			$ticket_log['person'] = App::getCurrentPerson();
			if ($name == 'ticket_created') {
				if (!$ticket_log['person'] OR !$ticket_log['person']['id']) {
					$ticket_log['person'] = $this->ticket->person;
				}
			}

			if (!$ticket_log['person'] OR !$ticket_log['person']['id']) {
				continue;
			}

			$ticket_log['ticket'] = $this->ticket;
			$ticket_log['action_type'] = $name;
			$ticket_log['details'] = $action->getLogDetails();

			App::getOrm()->persist($ticket_log);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		$events = $this->events;
		$log_actions = $this->entered_logs;

		// Clear current state
		$this->events = array();
		$this->entered_logs = array();

		if (!$this->is_performing) {
			$this->triggerEvents($events, $log_actions);
		}
	}

	public function triggerEvents($events, $log_actions)
	{
		App::getOrm()->beginTransaction();

		$notify_types = array();

		if (in_array('ticket_created', $events)) {
			$notify_types[] = 'new_ticket';

			if ($this->ticket['status'] != 'hidden') {
				$client_message = new Entity\ClientMessage();
				$client_message['channel'] = 'tickets.new-tickets';
				$client_message['data'] = array(
					'ticket_id' => $this->ticket['id'],
					'subject' => $this->ticket['subject']
				);

				App::getOrm()->persist($client_message);
			}
		}

		#------------------------------
		# New messages
		#------------------------------

		if (in_array('message_created', $events)) {

			$message = $log_actions['message_created']->getMessage();
			if (!$message['person']['is_agent'] OR $message['is_agent_note']) {
				$notify_types[] = 'new_reply';
			} else {
				$notify_types[] = 'new_agent_reply';
			}

			if ($this->ticket['status'] != 'hidden') {
				$client_message = new Entity\ClientMessage();
				$client_message['channel'] = 'tickets.new-messages';
				$client_message['data'] = array(
					'ticket_id' => $this->ticket['id'],
					'message_id' => $message['id']
				);

				App::getOrm()->persist($client_message);
			}
		}

		#------------------------------
		# Other changes
		#------------------------------

		if (in_array('property', $events)) {

			$notify_types[] = 'property_change';

			if ($this->ticket['status'] != 'hidden') {
				$client_message = new Entity\ClientMessage();
				$client_message['channel'] = 'tickets.updated';
				$client_message['data'] = array(
					'ticket_id' => $this->ticket['id']
				);

				App::getOrm()->persist($client_message);
			}
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		$this->sendAgentNotifications($events, $log_actions, $notify_types);
		$this->sendUserNotifications($events, $log_actions, $notify_types);
		$this->executeTriggers($events, $log_actions);
	}

	protected function sendUserNotifications($events, $log_actions, array $notify_types)
	{
		$ticket_email = new \Application\DeskPRO\Email\UserNotification\Ticket($this->ticket, $log_actions);
		$ticket_email->sendNotifications($notify_types);
	}

	protected function sendAgentNotifications($events, $log_actions, array $notify_types)
	{
		// No agents get notifications of hidden tickets
		if ($this->ticket->isHidden()) {
			return;
		}

		// If the ticket used to be waiting validation and now is open,
		// that means we need to send the newticket emails now
		if ($this->ticket['status'] == 'open' AND isset($log_actions['hidden_status'])) {
			$status_change = $log_actions['hidden_status']->getLogDetails();
			if ($status_change['old_status'] == 'validating') {
				$notify_types[] = 'new_ticket';
			}
		}

		if (!$notify_types) return;

		$matching_filters = array();

		$all_filters = App::getEntityRepository('DeskPRO:TicketFilter')->findAll();
		foreach ($all_filters as $q) {
			$searcher = $q->getSearcher();
			if ($searcher->doesTicketMatch($this->ticket)) {
				$matching_filters[] = $q['id'];
			}
		}

		if (!$matching_filters) return;

		$notifs = App::getEntityRepository('DeskPRO:AgentNotification')->getNotifications($matching_filters, $notify_types);
		if (!$notifs) return;

		$ticket_email = new \Application\DeskPRO\Email\AgentNotification\Ticket($this->ticket, $log_actions);
		$ticket_email->sendNotifications($notifs);
	}

	public function executeTriggers($events, $log_actions)
	{
		$trigger_events = array();
		if (in_array('new_ticket', $events)) {
			$trigger_events[] = 'new_ticket';
		}
		if (in_array('message_created', $events)) {
			$trigger_events[] = 'new_reply';
		}
		if (in_array('property_change', $events)) {
			$trigger_events[] = 'property_change';
		}

		$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getTriggersForEvents($trigger_events);
		$action_sets = array();
		foreach ($all_triggers as $trigger) {
			if ($trigger->checkTicketMatch($this->ticket, $log_actions)) {
				$action_sets[] = $trigger->getEditActions($this->ticket, $log_actions);
			}
		}

		$action_sets = Arrays::removeFalsey($action_sets);

		$this->is_performing = true;
		if ($action_sets) {
			App::getOrm()->beginTransaction();

			$ticket_edit = new \Application\DeskPRO\Tickets\TicketEdit($this->ticket);
			foreach ($action_sets as $actions) {
				$ticket_edit->applyActions($actions);
			}
			App::getOrm()->persist($this->ticket);

			App::getOrm()->flush();
			App::getOrm()->commit();
		}

		// Now loop again to perform external triggers
		foreach ($all_triggers as $trigger) {
			if ($trigger->checkTicketMatch($this->ticket, $log_actions)) {
				$trigger->performExternalActions($this->ticket, $log_actions);
			}
		}

		$this->is_performing = false;
	}
}