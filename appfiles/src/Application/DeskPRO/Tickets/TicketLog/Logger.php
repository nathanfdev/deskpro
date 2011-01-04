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
class Loggger implements Doctrine\Common\PropertyChangedListener
{
	protected $ticket;

	protected $entered_logs = array();

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		$this->log($prop, $old_val, $new_val);
	}

	public function log($prop, $old_val, $new_val)
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

			case 'Priority':
				$action = new Actions\Priority($old_val, $new_val);
				break;

			case 'Product':
				$action = new Actions\Product($old_val, $new_val);
				break;

			case 'status':
				$action = new Actions\Status($old_val, $new_val);
				break;
		}

		if ($action) {
			$this->entered_logs[$prop] = $action;
		}
	}

	public function saveLogs()
	{
		foreach ($this->entered_logs as $action) {
			$ticket_log = new Entity\TicketLog();
			$ticket_log['person'] = App::getCurrentPerson();
			$ticket_log['ticket'] = $this->ticket;
			$ticket_log['details'] = $action->getLogDetails();

			App::getOrm()->persist($ticket_log);
		}

		App::getOrm()->flush();
	}
}