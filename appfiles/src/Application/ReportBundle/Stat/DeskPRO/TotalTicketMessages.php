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
		$query = $this->createQuery()
		      ->select('COUNT(t.id) AS message_count')
		      ->from('tickets', 't')
		      ->innerJoin('t', 'tickets_messages', 'tm', 'tm.ticket_id = t.id');

		$this->addQuery('ticket_messages', $query);
	}

	public function processUngroupedResults($result)
	{
		return $result['ticket_messages'][0]['message_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results['ticket_messages'] as $result) {
			$processedResults[] = array(
				'value'       => $result['message_count'],
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}