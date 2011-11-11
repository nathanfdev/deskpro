<?php

namespace Application\ReportBundle\Stat\DeskPRO;

/**
 * Get the number of tickets awaiting agent
 */
class TicketsAwaitingAgent extends AbstractTicket
{
	public function buildConceptQueries()
	{
		$this->trendQueries[] =
		       "SELECT *
			FROM tickets
			WHERE status = 'open'";
	}

	public function processResults()
	{
		return $this->results[0];
	}
}