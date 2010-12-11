<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class TicketEdit
{
	protected $ticket;

	public function __construct(Entity\Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function addMessage(Entity\TicketMessage $message)
	{
		$this->ticket->addMessage($message);
	}

	public function save()
	{
		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush($this->ticket);
	}
}