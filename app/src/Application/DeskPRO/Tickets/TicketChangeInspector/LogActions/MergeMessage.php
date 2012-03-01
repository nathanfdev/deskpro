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

class MergeMessage implements LogActionInterface
{
	protected $message;
	protected $old_ticket_id;

	public function __construct($message, $old_ticket_id)
	{
		$this->message = $message;
		$this->old_ticket_id = $old_ticket_id;
	}

	public function getLogName()
	{
		return 'merged_message';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_ticket_id,
			'id_after'  => $this->message->ticket->id,
			'id_object' => $this->message->id
		);
	}

	public function getEventType()
	{
		return 'ticket_merge';
	}
}
