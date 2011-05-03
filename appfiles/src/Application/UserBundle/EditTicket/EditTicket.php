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

namespace Application\UserBundle\EditTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * New ticket acts as the processor and domain object for a newticket form
 */
class EditTicket
{
	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\TicketProps
	 */
	public $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	public $real_ticket;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = new TicketProps($ticket);
		$this->real_ticket = $ticket;
	}

	public function save()
	{
		
	}
}