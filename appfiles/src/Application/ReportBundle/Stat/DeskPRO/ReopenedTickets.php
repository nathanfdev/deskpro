<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets reopened
 */
class ReopenedTickets extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// Need to query the ticket log for this
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
	 * Get the formatter
	 *
	 * @return FormatterInterface
	 */
	public static function getFormatter()
	{
		$class = new \Application\ReportBundle\Stat\Formatter\IntegerFormatter();

		return $class;
	}
}