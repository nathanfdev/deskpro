<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the total amount of time a user is waiting for a response on their tickets
 */
class TotalUserWaitingResponseTime extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// AVG time
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

	/**
	 * Get the data formatter
	 *
	 * @return FormatterInterface
	 */
	public static function getFormatter()
	{
		$class = new \Application\ReportBundle\Stat\Formatter\TimeFormatter();

		return $class;
	}
}