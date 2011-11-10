<?php

namespace Application\ReportBundle\Stat\DeskPRO;

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