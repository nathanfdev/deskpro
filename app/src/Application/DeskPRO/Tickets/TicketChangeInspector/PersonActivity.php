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
use Application\DeskPRO\Entity;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewTicket as NewTicketAction;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewTicketReply as NewTicketReplyAction;

class PersonActivity
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
		if ($this->tracker->isExtraSet('ticket_created')) {
			$action = new NewTicketAction($this->ticket->person, $this->ticket);
			App::getPersonActivityLogger()->saveAction($action);
		} elseif ($this->tracker->isPropertyChanged('messages')) {
			$message_info = $this->tracker->getChangedProperty('messages');
			$message_info = array_pop($message_info); //array wrapper, possible for multi

			// New val means a new ticket (ie not delete)
			if ($message_info['new']) {
				$message = $message_info['new'];
				$action = new NewTicketReplyAction($message->person, $message);
				App::getPersonActivityLogger()->saveAction($action);
			}
		}
	}
}