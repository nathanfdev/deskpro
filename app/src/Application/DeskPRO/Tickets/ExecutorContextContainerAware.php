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

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Filters\FilterChangeDetector;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;

class ExecutorContextContainerAware extends ExecutorContext implements ExecutorContextContainerAwareInterface
{
	/**
	 * @var DeskproContainer
	 */
	private $container;

	private $cache_filter_change_detector;
	private $cache_filter_change_detector_version = 0;


	/**
	 * @param $container DeskproContainer
	 */
	public function setContainer(DeskproContainer $container)
	{
		$this->container = $container;
	}


	/**
	 * @return DeskproContainer
	 * @throws \LogicException When no container has been set yet
	 */
	public function getContainer()
	{
		if (!$this->container) {
			throw new \LogicException("Container has not been set");
		}
		return $this->container;
	}

	/**
	 * @param Ticket $ticket
	 * @return FilterChangeDetector
	 */
	public function createFilterChangeDetector(Ticket $ticket)
	{
		// Micro-optimisation to prevent two change detectors needing to run right after another.
		if ($this->cache_filter_change_detector_version == $ticket->getStateChangeRecorder()->getStateVersion()) {
			return $this->cache_filter_change_detector;
		}

		$em = $this->getContainer()->getEm();
		$filters = $em->getRepository('DeskPRO:TicketFilter')->getFilters();
		$agents  = $em->getRepository('DeskPRO:Person')->getAgents();

		$detector = new FilterChangeDetector($ticket, $filters, $agents);
		$detector->setLogger($this->getLogger());

		$this->cache_filter_change_detector = $detector;
		$this->cache_filter_change_detector_version = $ticket->getStateChangeRecorder()->getStateVersion();

		return $detector;
	}


	/**
	 * @param Ticket $ticket
	 * @return AgentNotifyListBuilder
	 */
	public function createNotifyListBuilder(Ticket $ticket)
	{
		$list_builder = new AgentNotifyListBuilder(
			$ticket,
			$this->createFilterChangeDetector($ticket),
			$this->getContainer()->getEm()->getRepository('DeskPRO:TicketFilterSubscription')
		);

		return $list_builder;
	}
}