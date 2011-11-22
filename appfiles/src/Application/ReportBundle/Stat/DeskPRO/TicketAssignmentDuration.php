<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Length of time until a ticket is assigned to an agent
 */
class TicketAssignmentDuration extends AbstractTicket
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
		// Need to query the ticket log for this, Interested in the AVG time
		$query = $this->createQuery();

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{

	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		return $processedResults;
	}
}