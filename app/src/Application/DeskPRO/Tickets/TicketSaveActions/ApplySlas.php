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
	 * @param \Application\DeskPRO\Entity\Sla[] $slas
	 * @param EntityManager $em
	 */
	public function __construct(array $slas, EntityManager $em)
	{
		$this->slas = Arrays::keyFromData($slas, 'id');
		$this->em = $em;
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
			} else {
				$context->getLogger()->debug(sprintf("[ApplySlas] SLA %d %s -- no match", $sla->id, $sla->title));
			}
		}
	}
}