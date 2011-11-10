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

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Merge implements LogActionInterface
{
	protected $ticket;
	protected $old_ticket;

	public function __construct($ticket, $old_ticket)
	{
		$this->ticket = $ticket;
		$this->old_ticket = $old_ticket;
	}

	public function getLogName()
	{
		return 'ticket_merge';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_ticket['id'] ?: null,
			'id_after'  => $this->new_ticket['id'] ?: null,

			'other_ticket_id' => $this->old_ticket['id']
		);
	}

	public function getEventType()
	{
		return 'ticket_merge';
	}
}
