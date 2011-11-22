<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the amount of time a ticket is waiting its first response
 */
class TicketFirstResponseTime extends AbstractTicket
{
	public function init()
	{
		parent::init();

		$this->addAvailableGroups(array(
			'department'	=> 'Department',
			'category'	=> 'Category',
			'priority'	=> 'Proprity',
			'workflow'	=> 'Workflow',
			'language'	=> 'Language',
			'agent'		=> 'Agent',
			'agent_team'	=> 'Agent Team',
			'user_id'	=> 'User',
			'rating'	=> 'Rating',
		));
	}

	public function buildConceptQueries()
	{
		// AVG time
		// Get the total time for first response
		$query = $this->createQuery()
		      ->select('SUM(tickets.total_to_first_reply) AS first_response_total')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_first_agent_reply) > :date_first_agent_reply')
		      ->setParameter(':date_first_agent_reply', $this->last_stat_date->format('U'));
		$this->addQuery('ticket_first_response_time', $query);

		// Get the number of tickets resolved
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) as ticket_count')
		      ->where("tickets.date_resolved IS NOT NULL")
		      ->andWhere('UNIX_TIMESTAMP(tickets.date_first_agent_reply) > :date_first_agent_reply')
		      ->setParameter(':date_first_agent_reply', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_replied_to', $query);
	}

	public function processUngroupedResults($result)
	{
		$waitingTime 	= $result['ticket_first_response_time'][0]['first_response_total'];
		$replied 	= $result['tickets_replied_to'][0]['ticket_count'];

		return ($replied != 0) ? $waitingTime / $replied : 0;
	}

	public function processGroupedResults($results)
	{
		// Get the Ids and result of the replied to tickets, we use this
		// array as a lookup based on the grouping_id
		$resultsRepliedTo = array();
		foreach ($results['tickets_replied_to'] as $result) {
			$resultsRepliedTo[$result[str_replace('.', '_', $this->grouping[0])]] = $result['ticket_count'];
		}

		$processedResults = array();

		foreach ($results['ticket_first_response_time'] as $result) {
			$groupingId = $result[str_replace('.', '_', $this->grouping[0])];

			// Check to see if any tickets were replied to for this grouping_id
			$repliedCount = 0;
			if (isset($resultsRepliedTo[$groupingId])) {
				$repliedCount = $resultsRepliedTo[$groupingId];
			}

			$processedResults[] = array(
				'value'       => ($repliedCount != 0) ? $result['first_response_total'] / $repliedCount : 0,
				'grouping_id' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

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