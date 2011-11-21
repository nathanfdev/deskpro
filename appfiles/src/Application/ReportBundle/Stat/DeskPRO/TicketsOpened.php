<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets opended in a period
 */
class TicketsOpened extends AbstractTicket
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
		// timestamp_opened < x < NOW
		$query = $this->createQuery()
		      ->select('COUNT(t.id) AS ticket_count')
		      ->from('tickets', 't')
		      ->where('UNIX_TIMESTAMP(t.date_created) >= :date_created')
		      ->setParameter(':date_created', $this->last_stat_date->format('U'));

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{
		var_dump($result);
	}

	public function processGroupedResults($results)
	{
		var_dump($results);
		$processedResults = array();

		return $processedResults;
	}
}