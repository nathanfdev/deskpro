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