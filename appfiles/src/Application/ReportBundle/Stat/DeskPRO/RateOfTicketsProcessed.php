<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the rate of tickets opened/closed
 */
class RateOfTicketsProcessed extends AbstractTicket
{
	public function buildConceptQueries()
	{
		// Number of tickets opened
		$this->trendQueries[] = "";

		// NUmber of tickets closed
		$this->trendQueries[] = "";
	}

	public function processResults()
	{
		return $this->results[0] / $this->results[1];
	}
}