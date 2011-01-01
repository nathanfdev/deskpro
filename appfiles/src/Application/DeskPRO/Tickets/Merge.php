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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class TicketMerge
{
	protected $ticket;
	protected $old_ticket;

	public function __construct(Entity\Ticket $ticket, Entity\Ticket $old_ticket)
	{
		$this->ticket = $ticket;
		$this->old_ticket = $old_ticket;
	}
}