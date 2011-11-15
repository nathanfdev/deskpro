<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets an agent participated in
 */
class TicketsAgentParticipate extends AbstractTicket
{
	public function init()
	{

	}
	
	public function buildConceptQueries()
	{
		$query = new QueryBuilder();
		$query->addSelect('COUNT(tickets_messages.id) AS message_count');
		$query->addFrom('tickets_messages');
		$query->addJoin('INNER JOIN tickets ON tickets.id = tickets_messages.ticket_id');

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{
		return $result[0]['message_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results as $result) {
			$processedResults[] = array(
				'value'       => $result['message_count'],
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}