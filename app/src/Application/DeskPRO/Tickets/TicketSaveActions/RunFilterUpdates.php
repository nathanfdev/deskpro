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

use Application\DeskPRO\Tickets\Filters\FilterChangeDetector;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\ORM\EntityManager;

class RunFilterUpdates implements TicketSaveActionInterface, ErrorCheckedInterface
{
	/**
	 * @var FilterChangeDetector
	 */
	private $filter_change_detector;

	/**
	 * @var EntityManager
	 */
	private $em;


	/**
	 * @param EntityManager        $em
	 * @param FilterChangeDetector $filter_change_detector
	 */
	public function __construct(EntityManager $em, FilterChangeDetector $filter_change_detector)
	{
		$this->em = $em;
		$this->filter_change_detector = $filter_change_detector;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		$change_set = $this->filter_change_detector->getFilterChangeSet($ticket, $context);
		$client_messages = $change_set->getListUpdateClientMessages();

		foreach ($client_messages as $cm) {
			$this->em->persist($cm);
		}
	}
}