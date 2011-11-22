<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the rate of tickets processed. This is the rate of tickets opened
 * vs resolved
 */
class RateOfTicketsProcessed extends AbstractTicket
{
	public function init()
	{
		parent::init();

		$this->addAvailableGroups(array(
			'department'	=> 'Department',
			'category'	=> 'Category',
			'priority'	=> 'Proprity',
			'workflow'	=> 'Workflow',
			'language'	=> 'Language',
			'agent'		=> 'Agent',
			'agent_team'	=> 'Agent Team',
			'user_id'	=> 'User',
			'rating'	=> 'Rating',
		));
	}

	public function buildConceptQueries()
	{
		// Rate = number of tickets opened / number of tickets resolved

		// Get the opened ticket since the last check
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->where('UNIX_TIMESTAMP(tickets.date_created) > :date_created')
		      ->setParameter(':date_created', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_opened', $query);

		// Get the resolved tickets since the last check
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->where('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_resolved', $query);

	}

	public function processUngroupedResults($result)
	{
		$opened 	= $result['tickets_opened'][0]['ticket_count'];
		$resolved 	= $result['tickets_resolved'][0]['ticket_count'];

		return ($resolved != 0) ? number_format($opened / $resolved, 2) : $opened;
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

		foreach ($results['tickets_opened'] as $result) {
			$groupingId = $result[str_replace('.', '_', $this->grouping[0])];

			// Check to see if any tickets were resolved for this grouping_id
			$resolvedCount = 0;
			if (isset($resultsResolved[$groupingId])) {
				$resolvedCount = $resultsResolved[$groupingId];
			}

			$processedResults[] = array(
				'value'       => ($resolvedCount != 0) ? number_format($result['ticket_count'] / $resolvedCount, 2) : $result['ticket_count'],
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}