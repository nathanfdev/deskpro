<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets awaiting agent
 */
class TicketsAwaitingAgent extends AbstractTicket
{
	public function buildConceptQueries()
	{
		$query = new QueryBuilder();
		$query->addSelect('COUNT(id) AS ticket_count');
		$query->addFrom('tickets');
		$query->addWhere("status = 'awaiting_agent'");

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
				'grouping_id' => $result['agent_id'],
			);
		}

		return $processedResults;
	}
}