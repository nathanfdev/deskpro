<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets in 'awaiting_agent' status
 */
class TicketsAwaitingAgent extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// Get the number of tickets in 'awaiting_agent' status
		// TODO: remove the 'open' status check when online db has been
		// switch to use new 'awaiting_agent status
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->andWhere("(tickets.status = 'open' OR tickets.status = 'awaiting_agent')");

		$this->addQuery('awaiting_agent', $query);
	}

	public function processUngroupedResults($result)
	{
		return $result['awaiting_agent'][0]['ticket_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results['awaiting_agent'] as $result) {
			$processedResults[] = array(
				'value'       => $result['ticket_count'],
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}