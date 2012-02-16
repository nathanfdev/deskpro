<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;
use Application\DeskPRO\Entity\Ticket;

/**
 * Get the total amount of time a user is waiting for a response on their tickets
 */
class TotalUserWaitingResponseTime extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// Get the total time a user was waiting before resolution
		$query = $this->createQuery()
		      ->select('SUM(tickets.total_user_waiting) AS user_waiting')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'));
		$this->addQuery('ticket_waiting_time', $query);

		$status_open = Ticket::getStatusInt(Ticket::STATUS_OPEN);
		
		// Get the number of times the resolved tickets went into 'awaiting_user' status
		$query = $this->createQuery()
		      ->select('COUNT(tickets_logs.id) as status_change_count')
		      ->innerJoin('tickets', 'tickets_logs', 'tickets_logs', 'tickets_logs.ticket_id = tickets.id')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->andWhere('tickets_logs.action_type = :action_type')
		      ->andWhere('tickets_logs.id_after = :status_open_id')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'))
		      ->setParameter(':action_type', 'status_changed')
		      ->setParameter(':status_open_id', $status_open);

		$this->addQuery('tickets_status_count', $query);
	}

	public function processUngroupedResults($result)
	{
		$waitingTime 	= $result['ticket_waiting_time'][0]['user_waiting'];
		$statusCount 	= $result['tickets_status_count'][0]['status_change_count'];

		return ($statusCount != 0) ? $waitingTime / $statusCount : 0;
	}

	public function processGroupedResults($results)
	{
		// Get the Ids and result of the resolved tickets, we use this
		// array as a lookup based on the grouping_id
		$resultsStatusCount = array();
		foreach ($results['tickets_status_count'] as $result) {
			$resultsStatusCount[$result[str_replace('.', '_', $this->grouping[0])]] = $result['status_change_count'];
		}

		$processedResults = array();

		foreach ($results['ticket_waiting_time'] as $result) {
			$groupingId = $result[str_replace('.', '_', $this->grouping[0])];

			// Check to see if any tickets have a status change count
			$resolvedCount = 0;
			if (isset($resultsStatusCount[$groupingId])) {
				$resolvedCount = $resultsStatusCount[$groupingId];
			}

			$processedResults[] = array(
				'value'       => ($resolvedCount != 0) ? $result['user_waiting'] / $resolvedCount : 0,
				'grouping_ref' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}
		
		return $processedResults;
	}

	/**
	 * Get the data formatter
	 *
	 * @return FormatterInterface
	 */
	public static function getFormatter()
	{
		$class = new \Application\ReportBundle\Stat\Formatter\TimeFormatter();

		return $class;
	}
}