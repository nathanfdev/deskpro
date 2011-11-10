<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\AbstractStat;

class TicketsAwaitingAgent extends AbstractStat
{
	public function buildConceptQueries()
	{
		$this->trendQueries[] =
		       "SELECT *
			FROM tickets
			WHERE status = 'awaiting_agent'";
	}
	
	public function processResults()
	{
		return $this->results[0];
	}
}