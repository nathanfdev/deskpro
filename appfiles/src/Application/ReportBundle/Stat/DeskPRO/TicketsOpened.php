<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get number of tickets opened
 */
class TicketsOpened extends AbstractTicket
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