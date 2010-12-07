<?php

namespace Application\DeskPRO\Tickets;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

class Queues
{
	/**
	 * Find all queues a person can use.
	 *
	 * @param mixed $person Person or person ID
	 * @return array Collection of TicketQueue entities
	 */
	public function getQueuesForPerson($person)
	{
		return App::getOrm()
			->getRepository('DeskPRO:TicketQueue')
			->getQueuesForPerson($person);
	}


	
	/**
	 * Get a ticket queue from an ID
	 * @param int $ticket_queue_id
	 * @return TicketQueue
	 */
	public function getQueueFromId($ticket_queue_id)
	{
		return App::getOrm()
			->getRepository('DeskPRO:TicketQueue')
			->find($ticket_queue_id);
	}


	
	/**
	 * Get the number of results in a queue.
	 *
	 * @param TicketQueue $ticket_queue
	 * @return int
	 */
	public function getCountForQueue($ticket_queue)
	{
		$ticket_queue = App::getOrm()->getRepository('DeskPRO:TicketQueue')->getTicketQueueFromVar($ticket_queue);

		return $ticket_queue->getResultsCount();
	}



	/**
	 * Get the counts for each queue a person can see.
	 *
	 * @param mixed $person Person or person ID
	 * @return array
	 */
	public function getAllCountsForPersonQueues($person)
	{
		$coll = $this->getQueuesForPerson($person);
		return $this->getAllCountsForQueuesCollection($coll);
	}


	
	/**
	 * Get counts for each queue in a collection.
	 * 
	 * @param array $ticket_queues
	 * @return array
	 */
	public function getAllCountsForQueuesCollection($ticket_queues)
	{
		$counts = array();

		foreach ($ticket_queues as $ticket_queue) {
			$counts[$ticket_queue['id']] = $ticket_queue->getResultsCount();
		}

		return $counts;
	}


	
	/**
	 * Get ticket results from a queue
	 * 
	 * @param TicketQueue $ticket_queue
	 * @param int $page
	 * @param int $per_page
	 * @return array
	 */
	public function getTicketsFromQueue($ticket_queue, $page = 1, $per_page = 25)
	{
		$ticket_queue = App::getOrm()->getRepository('DeskPRO:TicketQueue')->getTicketQueueFromVar($ticket_queue);

		$result_ids = $ticket_queue->getResults();

		if ($per_page) {
			$result_ids = array_chunk($result_ids, $per_page);
		} else {
			$result_ids = array($result_ids);
		}

		// index is 0-based
		$page--;

		if (!isset($result_ids[$page])) {
			return array();
		}

		$page_ids = $result_ids[$page];

		return App::getOrm()
			->getRepository('DeskPRO:Ticket')
			->getTicketsFromIds($page_ids);
	}
}