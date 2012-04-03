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

namespace Application\DeskPRO\Tickets\TicketChangeInspector;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

use Orb\Util\Arrays;

class TriggerExecutor
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $listener;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var array
	 */
	protected $event_types = array();

	protected $is_performing = false;
	protected $is_cancelled = false;

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
		$this->ticket = $tracker->getTicket();
	}

	/**
	 * @return \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	public function getChangeTracker()
	{
		return $this->tracker;
	}

	/**
	 * @return array
	 */
	public function getEventTypes()
	{
		return $this->event_types;
	}

	public function runPre()
	{
		if ($this->is_performing) return;
		$this->is_performing = true;

		$ticket_created_trigger = null;
		if ($this->tracker->isExtraSet('ticket_created')) {
			$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
			$trigger->terms = array();
			$trigger->actions = array(
				array('type' => 'new_ticket', 'options' => array())
			);

			$ticket_created_trigger = $trigger;
		}

		$all_triggers = array();

		if ($ticket_created_trigger) {
			array_unshift($all_triggers, $ticket_created_trigger);
		}

		$factory = new \Application\DeskPRO\Tickets\TicketActions\ActionsFactory();
		$factory->addGlobalOption('tracker', $this->tracker);
		$factory->addGlobalOption('ticket', $this->tracker->getTicket());

		$actions_collection = new ActionsCollection();

		foreach ($all_triggers as $trigger) {
			if ($trigger->isTriggerMatch($this->tracker->getTicket(), $this->tracker)) {
				$this->tracker->logMessage("[TriggerExecutor] Executing trigger {$trigger->id} {$trigger->event_trigger} " . print_r($trigger->terms,true) . " " . print_r($trigger->actions, true));

				foreach ($trigger['actions'] as $action_info) {
					$action = $factory->createFromInfo($action_info);
					if ($action) {
						$actions_collection->add($action);
						$this->tracker->recordExtraMulti('trigger', $trigger);
					}
				}
			}

			if ($actions_collection->hasModifierType('StopActions')) {
				break;
			}
		}

		$person = App::getCurrentPerson();
		if (!$person) {
			$person = $this->tracker->getTicket()->person;
		}
		$actions_collection->apply($this->tracker->getTicket(), $person);

		if ($actions_collection->isBroken()) {
			$this->is_cancelled = true;
		}

		$this->is_performing = false;
	}

	public function run()
	{
		if ($this->is_performing || $this->is_cancelled) return;

		$this->tracker->logMessage('[TriggerExecutor] run');

		$this->is_performing = true;

		$status_change  = $this->tracker->getChangedProperty('status');
		$hstatus_change = $this->tracker->getChangedProperty('hidden_status');

		// If we've just validated, then we'll send off a fake
		// ticket_created event for the TriggerExecutor
		if (!$this->tracker->isExtraSet('ticket_created') && $this->ticket->status_code == 'awaiting_agent' && ($status_change['old'] == 'hidden' && $hstatus_change['old'] == 'validating')) {
			$this->tracker->logMessage('[TriggerExecutor] ticket_created true');
			$this->tracker->recordExtra('ticket_created', true);
		}

		// Mark that is a validating ticket created, created triggers
		// will be ignored in the TriggerExecutor
		if ($this->ticket->status_code == 'hidden.validating') {
			$this->tracker->logMessage('[TriggerExecutor] ticket_created_validating true');
			$this->tracker->recordExtra('ticket_created_validating', true);
		}

		#------------------------------
		# Handle built-in events
		#------------------------------

		if ($this->tracker->isExtraSet('ticket_created')) {
			$this->event_types[] = 'new_ticket';
		} else {
			$this->event_types[] = 'property_change';

			if ($this->tracker->isPropertyChanged('messages')) {
				$this->event_types[] = 'new_reply';
			}
		}

		$this->tracker->logMessage('[TriggerExecutor] Events: ' . implode(', ', $this->event_types));

		$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getTriggersForEvents($this->event_types);

		// Note that the "built in" triggers below for notifications,
		// its important that they're array_unshift'ed onto the BEGINNING
		// of the $all_triggers array
		// This is because they can be modified like any other trigger,
		// so we dont want them added at the end after modifiers
		// are already run. For example: Template overrides, disabling notifications,
		// adding more users to notifications, etc.

		#------------------------------
		# Notify the user of course
		#------------------------------

		if ($this->tracker->isExtraSet('ticket_created')) {
			// Handled in preRun
		} elseif ($this->tracker->hasNewAgentReply()) {
			$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
			$trigger->terms = array();
			$trigger->actions = array(
				array('type' => 'user_notification_new_reply_agent', 'options' => array())
			);

			array_unshift($all_triggers, $trigger);
		} elseif ($this->tracker->hasNewUserReply()) {
			$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
			$trigger->terms = array();
			$trigger->actions = array(
				array('type' => 'user_notification_new_reply_user', 'options' => array())
			);

			array_unshift($all_triggers, $trigger);
		}

		#------------------------------
		# Add built-in agent notifications based off prefs
		#------------------------------

		$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
		$trigger->terms = array();
		$trigger->actions = array(
			array('type' => 'agent_alert_notification', 'options' => array())
		);

		array_unshift($all_triggers, $trigger);

		$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
		$trigger->terms = array();
		$trigger->actions = array(
			array('type' => 'agent_notification', 'options' => array())
		);

		array_unshift($all_triggers, $trigger);

		#------------------------------
		# Handle vacation mode agent
		#------------------------------

		// If the assigned agent is on vacation and the status is now awaiting_agent,
		// the ticket must be unasssigned

		if (($this->tracker->getTicket()->status == 'awaiting_agent' || $this->tracker->getTicket()->status == 'awaiting_user') && $this->tracker->getTicket()->agent && $this->tracker->getTicket()->agent->is_deleted) {
			$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
			$trigger->terms = array();
			$trigger->actions = array(
				array('type' => 'agent', 'options' => array('agent' => 0))
			);

			array_unshift($all_triggers, $trigger);
		}

		#------------------------------
		# Execute triggers
		#------------------------------

		$factory = new \Application\DeskPRO\Tickets\TicketActions\ActionsFactory();
		$factory->addGlobalOption('tracker', $this->tracker);
		$factory->addGlobalOption('ticket', $this->tracker->getTicket());

		$actions_collection = new ActionsCollection();

		$trigger_logs = array();

		foreach ($all_triggers as $trigger) {
			if ($trigger->isTriggerMatch($this->tracker->getTicket(), $this->tracker)) {
				$this->tracker->logMessage("[TriggerExecutor] Executing trigger {$trigger->id} {$trigger->event_trigger} " . print_r($trigger->terms,true) . " " . print_r($trigger->actions, true));

				foreach ($trigger['actions'] as $action_info) {
					$action = $factory->createFromInfo($action_info);
					if ($action) {
						$actions_collection->add($action);
						$this->tracker->recordExtraMulti('trigger', $trigger);
					}
				}
			}

			if ($actions_collection->hasModifierType('StopActions')) {
				break;
			}
		}

		#------------------------------
		# Flood checks / autoreply checks
		#------------------------------

		if (!DP_DEBUG) {
			$is_autoreply = false;

			if (in_array('new_ticket', $this->event_types)) {
				$timesnip = date('Y-m-d H:i:s', time() - App::getSetting('core_email.antiflood_newtickets_time'));
				$new_ticket_count = App::getDb()->fetchColumn("
					SELECT COUNT(*)
					FROM tickets
					WHERE person_id = ? AND date_created > ?
				", array($this->tracker->getTicket()->person->id, $timesnip));

				// If ticket is over antiflood, require validation
				if ($new_ticket_count > App::getSetting('core_email.antiflood_newtickets')) {
					$actions_collection->add($factory->create('delete', array()));

				// Lower threshold for turning off notificaiton
				} elseif ($new_ticket_count >= App::getSetting('core_email.antiflood_newtickets_warn')) {

					$actions_collection->add($factory->create('disable_user_notifications', array()));

					// If it is exactly the count, then send the warning email
					if ($new_ticket_count == App::getSetting('core_email.antiflood_newtickets_warn')) {
						$actions_collection->add($factory->create('warn_newticket_flood', array()));
					}
				}

				// Always disable user notificatiosn if message advertises itself as autoreply
				if ($this->ticket->email_reader && $this->ticket->email_reader->isFromRobot()) {
					$is_autoreply = true;
				}

			} elseif (in_array('new_reply', $this->event_types) && $this->tracker->hasNewUserReply()) {

				$timesnip = date('Y-m-d H:i:s', time() - App::getSetting('core_email.antiflood_newtickets_time'));
				$new_ticket_count = App::getDb()->fetchColumn("
					SELECT COUNT(*)
					FROM tickets_messages
					WHERE person_id = ? AND date_created > ?
				", array($this->tracker->getTicket()->person->id, $timesnip));

				// Dont send notifications to anyone now
				if ($new_ticket_count > App::getSetting('core_email.antiflood_newreplies')) {
					$actions_collection->add($factory->create('disable_notifications', array()));

				// Lower threshold for turning off user notificaiton to prevent loops
				} elseif ($new_ticket_count >= App::getSetting('core_email.antiflood_newreplies_warn')) {

					$actions_collection->add($factory->create('disable_user_notifications', array()));

					// If it is exactly the count, then send the warning email
					if ($new_ticket_count == App::getSetting('core_email.antiflood_newreplies_warn')) {
						$actions_collection->add($factory->create('warn_newticket_flood', array()));
					}
				}

				$messages = $this->getChangedProperty('messages');
				foreach ($messages as $m) {
					if ($m->email_reader && $m->email_reader->isFromRobot()) {
						$is_autoreply = true;
					}
				}
			}

			if ($is_autoreply) {
				$actions_collection->add($factory->create('disable_user_notifications', array()));
			}
		}

		#------------------------------
		# Execute triggers
		#------------------------------

		$person = App::getCurrentPerson();
		if (!$person) {
			$person = $this->tracker->getTicket()->person;
		}
		$actions_collection->apply($this->tracker->getTicket(), $person);



		$this->is_performing = false;
	}
}
