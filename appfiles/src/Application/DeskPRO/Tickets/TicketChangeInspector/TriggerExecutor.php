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

namespace Application\DeskPRO\Tickets\TicketChangeInspector;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

use Orb\Util\Arrays;

class TriggerExecutor
{
	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketChangeTracker
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

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
		$this->ticket = $tracker->getTicket();
	}

	/**
	 * @return \TicketChangeTracker\DeskPRO\Tickets\TicketChangeTracker
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

	public function run()
	{
		if ($this->is_performing) return;

		$this->is_performing = true;

		$status_change  = $this->tracker->getChangedProperty('status');
		$hstatus_change = $this->tracker->getChangedProperty('hidden_status');

		// If we've just validated, then we'll send off a fake
		// ticket_created event for the TriggerExecutor
		if (!$this->tracker->isExtraSet('ticket_created') && $this->ticket->status_code == 'open' && ($status_change['old'] == 'hidden' && $hstatus_change['old'] == 'validating')) {
			$this->tracker->recordExtra('ticket_created', true);
		}

		// Mark that is a validating ticket created, created triggers
		// will be ignored in the TriggerExecutor
		if ($this->ticket->status_code == 'hidden.validating') {
			$this->tracker->recordExtra('ticket_created_validating', true);
		}

		if ($this->tracker->isExtraSet('ticket_created_validating')) {
			// Validating means we dont run anything, except this hard-coded one that
			// sends the notify email :-)
			$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
			$trigger->terms = array();
			$trigger->actions = array(
				array('type' => 'user_notification_new_ticket_validating', 'options' => array())
			);

			$all_triggers = array($trigger);
		} else {
			if ($this->tracker->isExtraSet('ticket_created')) {
				$this->event_types[] = 'new_ticket';
			} else {
				$this->event_types[] = 'property_change';

				if ($this->tracker->isPropertyChanged('messages')) {
					$this->event_types[] = 'new_reply';
				}
			}

			$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getTriggersForEvents($this->event_types);
		}

		$factory = new \Application\DeskPRO\Tickets\TicketActions\ActionsFactory();
		$factory->addGlobalOption('tracker', $this->tracker);
		$factory->addGlobalOption('ticket', $this->tracker->getTicket());

		$actions_collection = new ActionsCollection();

		foreach ($all_triggers as $trigger) {
			if ($trigger->isTriggerMatch($this->tracker->getTicket(), $this->tracker)) {
				foreach ($trigger['actions'] as $action_info) {
					$action = $factory->createFromInfo($action_info);
					if ($action) {
						$actions_collection->add($action);
					}
				}
			}
		}

		$person = App::getCurrentPerson();
		if (!$person) {
			$person = $this->tracker->getTicket()->person;
		}
		$actions_collection->apply($this->tracker->getTicket(), $person);

		$this->is_performing = false;
	}
}
