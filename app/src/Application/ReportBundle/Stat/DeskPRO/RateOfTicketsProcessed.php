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
 * Get the rate of tickets processed. This is the rate of tickets opened
 * vs resolved
 */
class RateOfTicketsProcessed extends AbstractTicket
{
	public static function getLabelName()
	{
		return 'Rate';
	}

	public function init()
	{
		parent::init();
	}

	public function buildConceptQueries()
	{
		// Rate = number of tickets opened / number of tickets resolved

		// Get the opened ticket since the last check
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->where('UNIX_TIMESTAMP(tickets.date_created) > :date_created')
		      ->setParameter(':date_created', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_opened', $query);

		// Get the resolved tickets since the last check
		$query = $this->createQuery()
		      ->select('COUNT(tickets.id) AS ticket_count')
		      ->where('UNIX_TIMESTAMP(tickets.date_resolved) > :date_resolved')
		      ->setParameter(':date_resolved', $this->last_stat_date->format('U'));

		$this->addQuery('tickets_resolved', $query);

	}

	public function processUngroupedResults($result)
	{
		$opened 	= $result['tickets_opened'][0]['ticket_count'];
		$resolved 	= $result['tickets_resolved'][0]['ticket_count'];

		return ($resolved != 0) ? number_format($opened / $resolved, 2) : $opened;
	}

	public function processGroupedResults($results)
	{
		// Get the Ids and result of the resolved tickets, we use this
		// array as a lookup based on the grouping_id
		$resultsResolved = array();
		foreach ($results['tickets_resolved'] as $result) {
			$resultsResolved[$result[str_replace('.', '_', $this->grouping[0])]] = $result['ticket_count'];
		}

		$processedResults = array();

		foreach ($results['tickets_opened'] as $result) {
			$groupingId = $result[str_replace('.', '_', $this->grouping[0])];

			// Check to see if any tickets were resolved for this grouping_id
			$resolvedCount = 0;
			if (isset($resultsResolved[$groupingId])) {
				$resolvedCount = $resultsResolved[$groupingId];
			}

			$processedResults[] = array(
				'value'       => ($resolvedCount != 0) ? number_format($result['ticket_count'] / $resolvedCount, 2) : $result['ticket_count'],
				'grouping_ref' => $result[str_replace('.', '_', $this->grouping[0])],
			);
		}

		return $processedResults;
	}
}