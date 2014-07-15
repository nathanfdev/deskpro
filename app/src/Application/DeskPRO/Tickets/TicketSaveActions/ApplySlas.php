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
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class ApplySlas implements TicketSaveActionInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Sla[]
	 */
	private $slas;

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var $cm_sender
	 */
	private $cm_sender;


	/**
	 * @param array $slas
	 * @param EntityManager $em
	 * @param SlaClientMessageSender $cm_sender
	 */
	public function __construct(array $slas, EntityManager $em, SlaClientMessageSender $cm_sender)
	{
		$this->slas      = Arrays::keyFromData($slas, 'id');
		$this->em        = $em;
		$this->cm_sender = $cm_sender;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @return void
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($context->getEventType() != 'newticket' || !$this->slas) {
			return;
		}

		if (count($ticket->ticket_slas)) {
			$has_slas = array_map(function($s) { return $s->sla->id; }, is_array($ticket->ticket_slas) ? $ticket->ticket_slas : $ticket->ticket_slas->toArray());
			$has_slas = array_combine($has_slas, $has_slas);
		} else {
			$has_slas = array();
		}

		$context->getLogger()->info(sprintf("[ApplySlas] Testing %d SLAs", count($this->slas)));

		foreach ($this->slas as $sla) {
			if (isset($has_slas[$sla->id])) {
				$context->getLogger()->debug(sprintf("[ApplySlas] SLA %d %s -- already exists", $sla->id, $sla->title));
				continue;
			}

			if ($sla->apply_type == 'all' || ($sla->apply_type == 'terms' && $sla->apply_terms->isTriggerMatch($ticket, $context))) {
				$context->getLogger()->debug(sprintf("[ApplySlas] SLA %d %s -- added", $sla->id, $sla->title));
				$ticket_sla = $ticket->addSla($sla);
				$this->em->persist($ticket_sla);
				$this->cm_sender->sendMessage($ticket, $ticket_sla, $ticket_sla->sla_status, $ticket_sla->is_completed);
			} else {
				$context->getLogger()->debug(sprintf("[ApplySlas] SLA %d %s -- no match", $sla->id, $sla->title));
			}
		}

		$this->cm_sender->sendQueue();
	}
}