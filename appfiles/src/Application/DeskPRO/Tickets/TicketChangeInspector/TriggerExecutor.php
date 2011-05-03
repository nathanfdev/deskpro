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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

use \Orb\Util\Arrays;

class TriggerExecutor
{
	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $listener;

	/**
	 * @var array
	 */
	protected $event_types = array();

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
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

		if ($this->tracker->isExtraSet('ticket_created')) {
			$this->event_types[] = 'new_ticket';
		} else {
			$this->event_types[] = 'property_change';

			if ($this->tracker->isPropertyChanged('messages')) {
				$this->event_types[] = 'new_reply';
			}
		}

		$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getTriggersForEvents($this->event_types);
		$action_sets = array();
		foreach ($all_triggers as $trigger) {
			if ($trigger->checkTicketMatch($this->ticket, $log_actions)) {
				$action_sets[] = $trigger->getEditActions($this->ticket, $this);
			}
		}

		$action_sets = Arrays::removeFalsey($action_sets);

		$actions_collection = new ActionsCollection();
		foreach ($action_sets as $set) {
			foreach ($set as $act) {
				$actions_collection->add($act);
			}
		}

		$actions_collection->apply($this->ticket, App::getCurrentPerson());
		
		$this->is_performing = false;
	}
}