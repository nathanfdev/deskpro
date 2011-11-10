<?php

namespace Application\ReportBundle\Stat\DeskPRO;

/**
 * Get the time a ticket is assigned to someone
 */
class TicketAssignmentDuration extends AbstractTicket
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