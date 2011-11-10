<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\AbstractStat;

class RateOfTicketsProcessed extends AbstractStat
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