<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\Person;

class NewReply
{
	public $message;

	protected $ticket;
	protected $person;

	protected $ticket_message;

	public function __construct(Ticket $ticket, Person $person)
	{
		$this->ticket = $ticket;
		$this->person = $person;
	}

	public function save()
	{
		$ticket_message = new TicketMessage();
		$ticket_message['message'] = $this->message;
		$ticket_message->ticket = $this->ticket;
		$ticket_message->person = $this->person;

		$this->ticket->addMessage($ticket_message);

		// If status is pending, we'll switch it to open so agents will see it
		if ($this->ticket['status'] == Ticket::STATUS_PENDING) {
			$this->ticket['status'] = Ticket::STATUS_OPEN;
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($ticket_message);
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function getNewMessage()
	{
		return $this->ticket_message;
	}
}