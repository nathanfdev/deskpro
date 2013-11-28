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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Notifications;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketChangeInspector\DetectFilterMatches;

class TicketNotifyList
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var \Application\DeskPRO\Tickets\ExecutorContext
	 */
	private $context;

	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeInspector\DetectFilterMatches
	 */
	private $filter_detector;

	/**
	 * @var \Application\DeskPRO\Entity\TicketFilterSubscription[]
	 */
	private $subs;

	/**
	 * @var array
	 */
	private $notify_list;

	/**
	 * @param Ticket $ticket
	 * @param ExecutorContext $context
	 * @param DetectFilterMatches $filter_detector
	 * @param \Application\DeskPRO\Entity\TicketFilterSubscription[] $subs
	 */
	public function __construct(Ticket $ticket, ExecutorContext $context, DetectFilterMatches $filter_detector, array $subs)
	{
		$this->ticket          = $ticket;
		$this->context         = $context;
		$this->filter_detector = $filter_detector;
		$this->subs            = $subs;
	}

	public function getNotifyList()
	{
		if ($this->notify_list !== null) {
			return $this->notify_list;
		}

		$state = $this->ticket->getStateChangeRecorder();

		$orig_status = $this->ticket->status_code;
		if ($state->hasChangedField('status') || $state->hasChangedField('hidden_status')) {
			$start_status = $this->ticket->status;
			$statt_hstatus = $this->ticket->hidden_status;
		}

		$status_change         = $this->ticket->getStateChangeRecorder()->getFi('status');
		$hstatus_change        = $this->ticket->getStateChangeRecorder()->getChangedProperty('hidden_status');
		$assign_change         = $this->ticket->getStateChangeRecorder()->getChangedProperty('agent');
		$assign_team_change    = $this->ticket->getStateChangeRecorder()->getChangedProperty('agent_team');
		$assign_follow_change  = $this->ticket->getStateChangeRecorder()->getChangedProperty('participants');
	}
}