<?php

namespace Application\ReportBundle\Stat\DeskPRO;

/**
 * Get first resolution rate
 */
class FirstResolutionRate extends AbstractTicket
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