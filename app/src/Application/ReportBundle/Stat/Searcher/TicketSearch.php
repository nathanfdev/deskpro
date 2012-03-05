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

namespace Application\ReportBundle\Stat\Searcher;

use Application\DeskPRO\Searcher\TicketSearch as BaseTicketSearch;
use Application\ReportBundle\Stat\Base\QueryBuilder;

class TicketSearch extends BaseTicketSearch implements ReportSearchInterface
{
        /**
	 * Build the query, This is useful when you want a QueryBuilder instance
	 * of the Query instead of SQL. This will allow you to easily manipulate
	 * the Query elsewhere.
	 *
	 * Important: This does not care about ordering, or applying permissions.
	 * They are features not yet implemented, see the
	 * Application\DeskPRO\Searcher\TicketSearch::getSql() method for details
	 * on how to port these features to this method
	 *
	 * @param QueryBuilder $query
	 *
	 * @return QueryBuilder
	 */
	public function buildQuery(QueryBuilder $query)
	{
		$ticket_parts = $this->getSqlParts();
		$user_parts = null;
		if ($this->person_search) {
			$user_parts = $this->person_search->getSqlParts();
		}

		$query->select('tickets.id');
		$query->from('tickets', 'tickets');

		/**
		 * Add on the joins
		 */
		foreach ($ticket_parts['joins'] as $j) {
			if (is_array($j)) {
				$query->addJoin('tickets', $j[1]);
			} else {
				$query->leftJoin('tickets', $j, $j, "$j.ticket_id = tickets.id");
			}
		}

		if ($user_parts) {
			$query->leftJoin('tickets', 'people', 'people', "people.id = tickets.person");
		}

		if ($user_parts AND $user_parts['joins']) {

			foreach ($user_parts['joins'] as $j) {
				if (is_array($j)) {
					$query->addJoin('people', $j[1]);
				} else {
					$query->leftJoin('people', $j, $j, "$j.person_id = people.id");
				}
			}
		}

		/**
		 * Add on the wheres
		 */
		if (!empty($ticket_parts['wheres'])) {
			foreach ($ticket_parts['wheres'] as $where) {
				if ("tickets.status != 'hidden'" != $where) {
					$query->andWhere($where);
				}
			}
		}
		if (!empty($user_parts['wheres'])) {
			foreach ($user_parts['wheres'] as $where) {
				$query->andWhere($where);
			}
		}

		return $query;
	}
}