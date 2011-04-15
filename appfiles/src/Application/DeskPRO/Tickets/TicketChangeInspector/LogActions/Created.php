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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class Created implements LogActionInterface
{
	protected $ticket;

	public function __construct($ticket)
	{
		$this->ticket = $ticket;
	}

	public function getLogName()
	{
		return 'ticket_created';
	}

	public function getLogDetails()
	{
		return array(
			'ticket_id' => $this->ticket['id']
		);
	}

	public function getEventType()
	{
		return 'ticket_created';
	}
}