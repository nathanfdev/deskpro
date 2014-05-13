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

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class RecalculateSlas implements TicketSaveActionInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;


	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
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

		$state = $ticket->getStateChangeRecorder();

		#------------------------------
		# Get what we should be doing
		#------------------------------

		$recalc = false;

		if (($state->isNewTicket() && !$ticket->hidden_status)) {
			$recalc = true;
		}
		if ($state->hasChangedField('status')) {
			$recalc = true;
		}
		if ($state->hasNewReply()) {
			$recalc = true;
		}

		if (!$recalc) {
			$context->getLogger()->info('[RecalculateSlas] No ops');
			return;
		}

		#------------------------------
		# Perform calcs
		#------------------------------

		foreach ($ticket->ticket_slas as $ticket_sla) {
			// Dont touch ones that have been specifically set
			if ($ticket_sla->is_completed_set) {
				continue;
			}

			$calc = $ticket_sla->sla->getCalculator();
			$ticket_sla->warn_date = $calc->calculateWarnDate($ticket);
			$ticket_sla->fail_date = $calc->calculateFailDate($ticket);

			$completed_date = $calc->calculateCompletedDate($ticket);
			if ($completed_date) {
				$ticket_sla->setIsCompleted(true, $completed_date);
			} else {
				$ticket_sla->setIsCompleted(false, $completed_date);
			}

			$this->em->persist($ticket_sla);
		}
	}
}