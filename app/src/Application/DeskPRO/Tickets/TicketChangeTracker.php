<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;

/**
 * This is a legacy adapter to handle old code that needs the old ticket logger.
 *
 * @deprecated
 */
class TicketChangeTracker
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function done()
	{
		$this->ticket->_autoProcessTicket();
	}

	public function recordExtra()
	{
		// ignore
	}

	public function recordMultiPropertyChanged()
	{
		// ignore
	}

	public function setLogger()
	{
		// ignore
	}

	public function setApplyingTrigger()
	{
		// ignore
	}

	public function getApplyingTrigger()
	{
		return null;
	}

	public function setApplyingSla()
	{
		// ignore
	}

	public function getApplyingSla()
	{
		return null;
	}

	public function getApplyingSlaStatus()
	{
		return null;
	}

	public function getTicket()
	{
		return $this->ticket;
	}

	public function isTriggerChangeField()
	{
		return false;
	}

	public function preDone()
	{

	}

	public function getLogMessagesAsString()
	{
		return '';
	}

	public function __call($n, $v)
	{
		throw new \BadMethodCallException();
	}
}