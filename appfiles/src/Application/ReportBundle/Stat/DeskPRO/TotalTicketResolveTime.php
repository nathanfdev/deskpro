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
		$query = new QueryBuilder();
		$query->addSelect('COUNT(tickets.id) AS ticket_count');
		$query->addFrom('tickets');
		$query->addWhere("(tickets.status = 'open' OR tickets.status = 'awaiting_agent')");

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{
		return rand(0, 130000);
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results as $result) {
			$processedResults[] = array(
				'value'       => rand(0, 130000),
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