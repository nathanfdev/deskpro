<?php

namespace Application\ReportBundle\Stat\DeskPRO;

/**
 * Get total tickets messages
 */
class TotalTicketMessages extends AbstractTicket
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