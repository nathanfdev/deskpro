<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the rate of tickets closed by a single agent reply (only one ticket message
 * by agent)
 */
class FirstResolutionRate extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		$query = $this->createQuery();

		$this->addQuery($query);
	}

	public function processUngroupedResults($result)
	{

	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		return $processedResults;
	}
}