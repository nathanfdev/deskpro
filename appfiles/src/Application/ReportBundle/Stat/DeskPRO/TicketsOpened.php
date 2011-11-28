<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the number of tickets opended in a period
 */
class TicketsOpened extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// timestamp_opened < x < NOW
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->where('UNIX_TIMESTAMP(tickets.date_created) > :date_created')
		      ->setParameter(':date_created', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_opened', $query);
	}

	public function processUngroupedResults($result)
	{
		return $result['tickets_opened'][0]['ticket_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results['tickets_opened'] as $result) {
			$processedResults[] = array(
				'value'       => $result['ticket_count'],
				'grouping_ref' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

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