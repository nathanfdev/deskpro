<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get total tickets messages
 */
class TotalTicketMessages extends AbstractTicket
{
	public function init()
	{

	}
	
	public function buildConceptQueries()
	{
		$query = new QueryBuilder();
		$query->addSelect('COUNT(tickets.id) AS ticket_count');
		$query->addFrom('tickets');
		$query->addJoin('INNER JOIN tickets_messages ON tickets_messages.ticket_id = tickets.id');

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{
		return $result[0]['ticket_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results as $result) {
			$processedResults[] = array(
				'value'       => $result['ticket_count'],
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}