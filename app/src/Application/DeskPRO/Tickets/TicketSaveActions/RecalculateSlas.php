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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Application\DeskPRO\Tickets\Slas\SlaProcessor;
use Application\DeskPRO\Tickets\Actions\ActionApplicatorInterface;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class RecalculateSlas implements TicketSaveActionInterface, ErrorCheckedInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var ActionApplicatorInterface
	 */
	private $action_applicator;



	/**
	 * @param EntityManager $em
	 * @param ActionApplicatorInterface $action_applicator
	 */
	public function __construct(EntityManager $em, ActionApplicatorInterface $action_applicator)
	{
		$this->em = $em;
		$this->action_applicator = $action_applicator;
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

		$cm_sender = new SlaClientMessageSender($this->em->getConnection());
		if ($context->getPersonContext() && $context->getPersonContext()->getId()) {
			$cm_sender->setPersonContext($context->getPersonContext());
		}

		$proc = new SlaProcessor($this->em, $this->action_applicator, $cm_sender);
		$proc->calculateSlas($ticket, $context);
	}
}