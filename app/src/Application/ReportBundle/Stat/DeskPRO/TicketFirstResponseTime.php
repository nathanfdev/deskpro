<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\QueryBuilder;

/**
 * Get the amount of time a ticket is waiting its first response
 */
class TicketFirstResponseTime extends AbstractTicket
{
	public static function getLabelName()
	{
		return 'Time';
	}

	public function init()
	{
		parent::init();
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
				'grouping_ref' => $result[str_replace('.', '_', $this->grouping[0])],
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