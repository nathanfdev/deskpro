<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get total tickets messages
 */
class TotalTicketMessages extends AbstractTicket
{
	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		$query = $this->createQuery()
		      ->select('COUNT(tickets_messages.id) AS message_count')
		      ->innerJoin('tickets', 'tickets_messages', 'tickets_messages', 'tickets_messages.ticket_id = tickets.id');

		$this->addQuery('ticket_messages', $query);
	}

	public function processUngroupedResults($result)
	{
		return $result['ticket_messages'][0]['message_count'];
	}

	public function processGroupedResults($results)
	{
		$processedResults = array();

		foreach ($results['ticket_messages'] as $result) {
			$processedResults[] = array(
				'value'       => $result['message_count'],
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