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

class MergeAttach implements LogActionInterface
{
	protected $attach;
	protected $old_ticket_id;

	public function __construct($attach, $old_ticket_id)
	{
		$this->attach = $attach;
		$this->old_ticket_id = $old_ticket_id;
	}

	public function getLogName()
	{
		return 'merged_attach';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_ticket_id,
			'id_after'  => $this->attach->ticket->id,
			'id_object' => $this->attach->id
		);
	}

	public function getEventType()
	{
		return 'ticket_merge';
	}
}
