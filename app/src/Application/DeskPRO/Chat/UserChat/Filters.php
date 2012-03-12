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

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;
use Application\DeskPRO\Entity\ChatConversationFilter;
use Symfony\Component\DependencyInjection\ContainerAware;

class Filters
{
	/**
	 * Find all filters a person can use.
	 *
	 * @param mixed $person Person or person ID
	 * @return array Collection of ChatConversationFilter entities
	 */
	public function getFiltersForPerson($person)
	{
		return App::getOrm()
			->getRepository('DeskPRO:ChatConversationFilter')
			->getFiltersForPerson($person);
	}


	public function getGroupedFiltersForPerson($person)
	{
		$all_filters = App::getApi('chatconversation.filters')->getFiltersForPerson($person);

		$order = $person->getPref('agent.ui.chatconversation-filters-order');
		if ($order) {
			$filters_unordered = $all_filters;
			$all_filters = array();

			foreach ($order as $id) {
				if (isset($filters_unordered[$id])) {
					$all_filters[$id] = $filters_unordered[$id];
					unset($filters_unordered[$id]);
				}
			}

			if (count($filters_unordered)) {
				foreach ($filters_unordered as $id => $q) {
					$all_filters[$id] = $q;
				}
			}
		}

		// Order them into sys/other
		$sys_filters = array();
		$sys_filters_hold = array();
		$custom_filters = array();

		foreach ($all_filters as $id => $filter) {
			if ($filter['sys_name']) {
				if (strpos($filter['sys_name'], '_w_hold')) {
					$sys_filters_hold[$filter['sys_name']] = $filter;
				} else {
				$sys_filters[$filter['sys_name']] = $filter;
				}
			} else {
				$custom_filters[$id] = $filter;
			}
		}

		// Force order of sys
		$sys_filters_unordered = $sys_filters;
		$sys_filters = array();
		foreach (array('agent', 'participant', 'agent_team', 'unassigned', 'all') as $id) {
			if (isset($sys_filters_unordered[$id])) {
				$sys_filters[$id] = $sys_filters_unordered[$id];
				unset($sys_filters_unordered[$id]);
			}
		}

		$sys_filters_unordered = $sys_filters_hold;
		$sys_filters_hold = array();
		foreach (array('agent', 'participant', 'agent_team', 'unassigned', 'all') as $id) {
			$id .= '_w_hold';
			if (isset($sys_filters_unordered[$id])) {
				$sys_filters_hold[$id] = $sys_filters_unordered[$id];
				unset($sys_filters_unordered[$id]);
			}
		}

		if (count($sys_filters_unordered)) {
			foreach ($sys_filters_unordered as $id => $q) {
				$sys_filters[$id] = $q;
			}
		}

		if (!$person->getHasTeams()) {
			unset($sys_filters['agent_team']);
			unset($sys_filters_unordered['agent_team_w_hold']);
			unset($sys_filters_hold['agent_team_w_hold']);
		}

		return array(
			'all_filters' => $all_filters,
			'sys_filters' => $sys_filters,
			'sys_filters_hold' => $sys_filters_hold,
			'custom_filters' => $custom_filters,
		);
	}


	/**
	 * Get a chatconversation filter from an ID
	 * @param int $chatconversation_filter_id
	 * @return ChatConversationFilter
	 */
	public function getFilterFromId($chatconversation_filter_id)
	{
		return App::getOrm()
			->getRepository('DeskPRO:ChatConversationFilter')
			->find($chatconversation_filter_id);
	}


	/**
	 * Get the number of results in a filter.
	 *
	 * @param TicketFilter $chatconversation_filter
	 * @return int
	 */
	public function getCountForFilter($chatconversation_filter)
	{
		$chatconversation_filter = App::getOrm()->getRepository('DeskPRO:TicketFilter')->getTicketFilterFromVar($chatconversation_filter);

		return $chatconversation_filter->getResultsCount();
	}


	/**
	 * Get the counts for each filter a person can see.
	 *
	 * @param mixed $person Person or person ID
	 * @return array
	 */
	public function getAllCountsSystemFilters($person)
	{
		$coll = App::getOrm()
			->getRepository('DeskPRO:TicketFilter')
			->getSystemFilters($person);

		return $this->getAllCountsForFiltersCollection($coll);
	}


	/**
	 * Get the counts for each custom filter a person can see.
	 *
	 * @param mixed $person Person or person ID
	 * @return array
	 */
	public function getAllCountsCustomFilters($person)
	{
		$coll = App::getOrm()
			->getRepository('DeskPRO:TicketFilter')
			->getCustomFiltersForPerson($person);

		return $this->getAllCountsForFiltersCollection($coll);
	}


	/**
	 * Get counts for each filter in a collection.
	 *
	 * @param array $chatconversation_filters
	 * @return array
	 */
	public function getAllCountsForFiltersCollection($chatconversation_filters)
	{
		$counts = array();

		foreach ($chatconversation_filters as $chatconversation_filter) {
			$counts[$chatconversation_filter['id']] = $chatconversation_filter->getResultsCount();
		}

		return $counts;
	}


	/**
	 * Get an array of IDs for each filter in a collection
	 *
	 * @param $chatconversation_filters
	 * @return array
	 */
	public function getAllIdsForFiltersCollection($chatconversation_filters)
	{
		$all_ids = array();

		foreach ($chatconversation_filters as $chatconversation_filter) {
			$all_ids[$chatconversation_filter['id']] = $chatconversation_filter->getResults();
		}

		return $all_ids;
	}


	/**
	 * Get an array of IDs for each filter in a collection
	 *
	 * @param $chatconversation_filters
	 * @return array
	 */
	public function getAllHoldIdsForFiltersCollection($chatconversation_filters)
	{
		$all_ids = array();

		foreach ($chatconversation_filters as $chatconversation_filter) {
			$searcher = $chatconversation_filter->getSearcher(array('type' => 'is_hold', 'op' => 'is', 'options' => array('is_hold' => 1)));
			$all_ids[$chatconversation_filter['id']] = $searcher->getResults();
		}

		return $all_ids;
	}


	/**
	 * @param $chatconversation_filter
	 * @return
	 */
	public function getIdsFromFilter($chatconversation_filter)
	{
		$chatconversation_filter = App::getOrm()->getRepository('DeskPRO:TicketFilter')->getTicketFilterFromVar($chatconversation_filter);

		$result_ids = $chatconversation_filter->getResults();

		return $result_ids;
	}


	/**
	 * Get chatconversation results from a filter
	 *
	 * @param TicketFilter $chatconversation_filter
	 * @param int $page
	 * @param int $per_page
	 * @return array
	 */
	public function getTicketsFromFilter($ticket_filter, $page = 1, $per_page = 25)
	{
		$ticket_filter = App::getOrm()->getRepository('DeskPRO:TicketFilter')->getTicketFilterFromVar($ticket_filter);

		$result_ids = $ticket_filter->getResults();

		if ($per_page) {
			$result_ids = array_chunk($result_ids, $per_page);
		} else {
			$result_ids = array($result_ids);
		}

		// index is 0-based
		$page--;

		if (!isset($result_ids[$page])) {
			return array();
		}

		$page_ids = $result_ids[$page];

		return App::getOrm()
			->getRepository('DeskPRO:Ticket')
			->getTicketsFromIds($page_ids);
	}


	/**
	 * Get flagged tickets
	 *
	 * @param string $flag
	 * @param Person $person
	 * @param int $page
	 * @param int $per_page
	 * @return array
	 */
	public function getTicketsFromFlagged($flag, $person, $page = 1, $per_page = 25)
	{
		$result_ids = App::getDb()->fetchAllCol("
			SELECT ticket_id
			FROM tickets_flagged
			WHERE person_id = ? AND color = ?
		", array($person['id'], $flag));

		if ($per_page) {
			$result_ids = array_chunk($result_ids, $per_page);
		} else {
			$result_ids = array($result_ids);
		}

		// index is 0-based
		$page = min(0, --$page);

		if (!isset($result_ids[$page])) {
			return array();
		}

		$page_ids = $result_ids[$page];

		return App::getOrm()
			->getRepository('DeskPRO:Ticket')
			->getTicketsFromIds($page_ids);
	}


	/**
	 * Get the counts for each flag a person has.
	 *
	 * @param mixed $person Person or person ID
	 * @return array
	 */
	public function getAllCountsForPersonFlagged($person)
	{
		$counts = App::getDb()->fetchAllKeyValue("
			SELECT color, COUNT(color)
			FROM tickets_flagged
			WHERE person_id = ?
			GROUP BY color
		", array($person['id']));

		return $counts;
	}
}
