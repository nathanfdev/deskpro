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

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Departments\TicketDepartments;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketManager;

class VerifyDepartment implements TicketSaveActionInterface
{
	/**
	 * @var TicketDepartments
	 */
	private $ticket_deps;


	/**
	 * @param TicketDepartments $ticket_deps
	 */
	public function __construct(TicketDepartments $ticket_deps)
	{
		$this->ticket_deps = $ticket_deps;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @return void
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($context->getEventType() == 'noop') {
			return;
		}

		if (!$ticket->department) {
			$dep = $this->ticket_deps->getDefaultDepartment();
			$context->getLogger()->info("Setting system default department: {$dep->id} {$dep->title}");
			$ticket->department = $dep;
		}

		if ($ticket->department && $this->ticket_deps->getChildren($ticket->department)) {
			$set = $ticket->department;
			$dep = $this->ticket_deps->getDefaultDepartment();
			$context->getLogger()->info("The set department {$set->id} {$set->title} has children. Reverting to system default: {$dep->id} {$dep->title}");
			$ticket->department = $dep;
		}
	}

}