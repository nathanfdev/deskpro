<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\ApiBundle\Controller;

use \DeskPRO\Domain\DomainObject;

/**
 * Perform searches or get results from queues.
 */
class TicketSearchController extends AbstractController
{
	/**
	 * Get a map of queues.
	 */
	public function getQueueNamesAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		return $this->createApiResponse(array(
			'queues' => $queues
		));
	}


	
	/**
	 * Get counts for all queues
	 */
	public function getQueueCountsAction()
	{
		$all_counts = $queues = App::getApi('tickets.queues')->getAllCountsForPersonQueues($this->person);

		return $this->createApiResponse(array(
			'counts' => $counts
		));
	}



	/**
	 * Execute a filter and return results.
	 * 
	 * @param int $queue_id
	 */
	public function getQueueResultsAction($queue_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$queue = App::getApi('tickets.queues')->getQueueFromId($queue_id);
		$num_results = $queue->getResultsCount();
		$num_pages = ceil($num_results / $per_page);

		$tickets = App::getApi('tickets.queues')->getTicketsFromQueue($queue_id, $page, $per_page);

		$ret = array();
		foreach ($tickets as $t) {
			$ret[] = $t->toArray(DomainObject::TOARRAY_DEEP & DomainObject::TOARRAY_LOAD_UNLOADED);
		}

		return $this->createApiResponse(array(
			'num_tickets' => $num_results,
			'num_pages' => $num_pages,
			'tickets' => $ret
		));
	}
}