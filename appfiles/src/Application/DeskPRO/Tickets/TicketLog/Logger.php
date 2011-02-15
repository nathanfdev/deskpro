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

/**
 * The ticket logger takes care of logging changes done to a ticket.
 */
class Logger implements \Doctrine\Common\PropertyChangedListener
{
	protected $ticket;

	protected $entered_logs = array();

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		$this->logChange($prop, $old_val, $new_val);
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

	public function saveLogs()
	{
		App::getOrm()->beginTransaction();

		foreach ($this->entered_logs as $name => $action) {
			$ticket_log = new Entity\TicketLog();
			$ticket_log['person'] = App::getCurrentPerson();
			$ticket_log['ticket'] = $this->ticket;
			$ticket_log['action_type'] = $name;
			$ticket_log['details'] = $action->getLogDetails();

			App::getOrm()->persist($ticket_log);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function triggerEvents()
	{
		App::getOrm()->beginTransaction();

		#------------------------------
		# New messages
		#------------------------------

		if (isset($this->entered_logs['message_created'])) {
			$client_message = new Entity\ClientMessage();
			$client_message['channel'] = 'tickets.new-messages';
			$client_message['data'] = array(
				'ticket_id' => $this->ticket['id'],
				'message_id' => $this->entered_logs['message_created']->getMessage()->getId()
			);

			App::getOrm()->persist($client_message);
		}

		#------------------------------
		# Other changes
		#------------------------------

		// Only trigger a general 'change' when we have
		// changes that arent new messages
		$num = count($this->entered_logs);
		if (isset($this->entered_logs['messages'])) $num--;

		if ($num) {
			$client_message = new Entity\ClientMessage();
			$client_message['channel'] = 'tickets.updated';
			$client_message['data'] = array(
				'ticket_id' => $this->ticket['id']
			);

			App::getOrm()->persist($client_message);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function reset()
	{
		$this->entered_logs = array();
	}
}