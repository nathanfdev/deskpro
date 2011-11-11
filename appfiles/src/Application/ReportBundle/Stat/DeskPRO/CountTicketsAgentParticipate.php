<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets an agent participated in
 */
class CountTicketsAgentParticipate extends AbstractTicket
{
	public function buildConceptQueries()
	{
		// Number of tickets opened
		$this->trendQueries[] = "";
	}

	public function processResults()
	{
		return $this->results[0];
	}
}