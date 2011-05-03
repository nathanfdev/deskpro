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

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\TicketChangeTracker;

class TriggerEventFilter
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketTrigger[]
	 */
	protected $triggers;
	
	public function __construct($triggers)
	{
		$this->triggers = $triggers;
	}

	/**
	 * Filter all the triggers through a trackr to get a final array of actions
	 * that sholud be executed.
	 *
	 * @param array               $event_types  An array of event types
	 * @param TicketChangeTracker $tracker
	 * @return array
	 */
	public function getActions(array $event_types, TicketChangeTracker $tracker)
	{
		$ticket = $tracker->getTicket();

		#------------------------------
		# Fetch all triggers that apply to this type
		#------------------------------

		$event_triggers = array();

		foreach ($this->triggers as $trigger) {
			if (in_array($tracker['event_trigger'], $event_types)) {
				$event_triggers[] = $tracker;
			}
		}

		#------------------------------
		# Now go through and fetch all that actually match the changes
		#------------------------------

		$actions = array();
	}
}