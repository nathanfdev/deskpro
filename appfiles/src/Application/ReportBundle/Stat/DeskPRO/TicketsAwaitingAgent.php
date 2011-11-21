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
		// Get the number of tickets in 'awaiting_agent' status
		// TODO: remove the 'open' status check when online db has been
		// switch to use new 'awaiting_agent status
		$query = $this->createQuery()
		      ->select('COUNT(t.id) AS ticket_count')
		      ->from('tickets', 't')
		      ->where("(t.status = 'open' OR t.status = 'awaiting_agent')");

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