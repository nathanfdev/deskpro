<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder as BaseStateChangeRecorder;

class StateChangeRecorder extends BaseStateChangeRecorder
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var bool
	 */
	private $no_id = false;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = $ticket;
		if (!$ticket->id) {
			$this->no_id = true;
		}
	}


	/**
	 * @return bool
	 */
	public function isNewTicket()
	{
		// If the ticket is not a proxy object it means it was created
		// now.
		// - If there was no ID at the time this state recorder was created,
		// it means its part of the same state transaction. (eg state recorder wasnt reset)
		if ($this->no_id && get_class($this) === 'Application\\DeskPRO\\Entity\\Ticket') {
			return true;
		}

		return false;
	}
}