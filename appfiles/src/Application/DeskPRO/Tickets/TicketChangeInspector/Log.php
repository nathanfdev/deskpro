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

class Log
{
	/**
	 * @var \TicketChangeTracker\DeskPRO\Tickets\TicketListener
	 */
	protected $tracker;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
		$this->ticket = $tracker->getTicket();
	}

	public function runPre()
	{
		// If we've got a new message, then we need to fetch previous
		// urgency resets since the last reply time.
		// If any are marked as reset, we'll need to apply that urgency
		// mod now

		$old_reply_time = $this->tracker->getChangedProperty('date_last_agent_reply');

		// Checking on date_last_user_reply means we can easily check
		// that there was a reply made just now, AND it was an agent reply
		if (!$old_reply_time) {
			return;
		}

		$old_reply_time = $old_reply_time['old'];

		// There was no old times
		if (!$old_reply_time) {
			return;
		}

		$logs = App::getOrm()->createQuery("
			SELECT l
			FROM DeskPRO:TicketLog l
			WHERE l.ticket = ?1 AND l.action_type = ?2 AND l.date_created >= ?3
		")->setParameter(1, $this->ticket)
		  ->setParameter(2, 'changed_urgency')
		  ->setParameter(3, $old_reply_time->format('Y-m-d H:m:s'))
		  ->execute();

		$mod = 0;

		foreach ($logs as $log) {
			if (isset($log['details']['reset_next_reply'])) {
				$mod += $log['details']['reset_next_reply'];
			}
		}

		if ($mod) {
			$this->ticket->modifyUrgency($mod);
		}
	}

	public function run()
	{
		if ($this->tracker->isExtraSet('ticket_created')) {
			$action = new LogActions\Created($this->ticket);
			$this->addLogItem($action);
		}
		
		foreach ($this->tracker->getAllChangedProperties() as $prop => $info) {
			$action = null;

			$old_val = null;
			$new_val = null;

			if (isset($info['old'])) $old_val = $info['old'];
			if (isset($info['new'])) $new_val = $info['new'];

			switch ($prop) {
				case 'agent':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Agent($old_val, $new_val);
					}
					break;

				case 'category':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Category($old_val, $new_val);
					}
					break;

				case 'department':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Department($old_val, $new_val);
					}
					break;

				case 'messages':
					if ($new_val) {
						$action = new LogActions\Message($new_val);
					} else {
						// $old_val means removed
					}
					break;

				case 'priority':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Priority($old_val, $new_val);
					}
					break;

				case 'product':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Product($old_val, $new_val);
					}
					break;

				case 'status':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\Status($old_val, $new_val);
					}
					break;

				case 'hidden_status':
					if (!$this->tracker->isNewTicket()) {
						$action = new LogActions\HiddenStatus($old_val, $new_val);
					}
					break;
			}

			if (!$action) {
				continue;
			}

			$this->addLogItem($action);
		}

		App::getOrm()->flush();
	}

	protected function addLogItem($action)
	{
		$ticket_log = new Entity\TicketLog();
		$ticket_log['person'] = App::getCurrentPerson();

		// TODO should always have a person, but need to handle system events (time escalations)
		if (!$ticket_log['person'] OR !$ticket_log['person']['id']) {
			$ticket_log['person'] = $this->ticket->person;
		}

		$ticket_log['ticket'] = $this->ticket;
		$ticket_log['action_type'] = $action->getLogName();
		$ticket_log['details'] = $action->getLogDetails();

		App::getOrm()->persist($ticket_log);
	}
}