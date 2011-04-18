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

	public function run()
	{
		foreach ($this->tracker->getAllChangedProperties() as $prop => $info) {
			$action = null;

			$old_val = $info['old'];
			$new_val = $info['new'];

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

		App::getOrm()->flush();
	}
}