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
	protected $old_ticket_id;

	public function __construct($ticket, $old_ticket_id)
	{
		$this->ticket = $ticket;
		$this->old_ticket_id = $old_ticket_id;
	}

	public function getLogName()
	{
		return 'ticket_merge';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_ticket_id ?: null,
			'id_after'  => $this->ticket['id'] ?: null,
		);
	}

	public function getEventType()
	{
		return 'ticket_merge';
	}
}
