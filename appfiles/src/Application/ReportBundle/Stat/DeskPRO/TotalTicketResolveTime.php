<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the total ticket resolve time
 */
class TotalTicketResolveTime extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// Get the total time taken to resolve a ticket
		$query = $this->createQuery()
		      ->select('SUM(UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created)) AS resolve_time')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'));
		$this->addQuery('ticket_resolve_time', $query);

		// Get the number of tickets resolved
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) as ticket_count')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_resolved', $query);
	}

	public function processUngroupedResults($result)
	{
		$resolveTime 	= $result['ticket_resolve_time'][0]['resolve_time'];
		$resolved 	= $result['tickets_resolved'][0]['ticket_count'];

		return ($resolved != 0) ? $resolveTime / $resolved : 0;
	}

	public function processGroupedResults($results)
	{
		// Get the Ids and result of the resolved tickets, we use this
		// array as a lookup based on the grouping_id
		$resultsResolved = array();
		foreach ($results['tickets_resolved'] as $result) {
			$resultsResolved[$result[str_replace('.', '_', $this->grouping[0])]] = $result['ticket_count'];
		}

		$processedResults = array();

		foreach ($results['ticket_resolve_time'] as $result) {
			$groupingId = $result[str_replace('.', '_', $this->grouping[0])];

			// Check to see if any tickets were resolved for this grouping_id
			$resolvedCount = 0;
			if (isset($resultsResolved[$groupingId])) {
				$resolvedCount = $resultsResolved[$groupingId];
			}

			$processedResults[] = array(
				'value'       => ($resolvedCount != 0) ? $result['resolve_time'] / $resolvedCount : 0,
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
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