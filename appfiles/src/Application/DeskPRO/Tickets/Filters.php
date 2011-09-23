<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFilter;
use Symfony\Component\DependencyInjection\ContainerAware;

class Filters
{
	/**
	 * Find all filters a person can use.
	 *
	 * @param mixed $person Person or person ID
	 * @return array Collection of TicketFilter entities
	 */
	public function getFiltersForPerson($person)
	{
		return App::getOrm()
			->getRepository('DeskPRO:TicketFilter')
			->getFiltersForPerson($person);
	}


	/**
	 * Get a ticket filter from an ID
	 * @param int $ticket_filter_id
	 * @return TicketFilter
	 */
	public function getFilterFromId($ticket_filter_id)
	{
		return App::getOrm()
			->getRepository('DeskPRO:TicketFilter')
			->find($ticket_filter_id);
	}


	/**
	 * Get the number of results in a filter.
	 *
	 * @param TicketFilter $ticket_filter
	 * @return int
	 */
	public function getCountForFilter($ticket_filter)
	{
		$ticket_filter = App::getOrm()->getRepository('DeskPRO:TicketFilter')->getTicketFilterFromVar($ticket_filter);

		return $ticket_filter->getResultsCount();
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
	 * @param array $ticket_filters
	 * @return array
	 */
	public function getAllCountsForFiltersCollection($ticket_filters)
	{
		$counts = array();

		foreach ($ticket_filters as $ticket_filter) {
			$counts[$ticket_filter['id']] = $ticket_filter->getResultsCount();
		}

		return $counts;
	}


	/**
	 * Get an array of IDs for each filter in a collection
	 *
	 * @param $ticket_filters
	 * @return array
	 */
	public function getAllIdsForFiltersCollection($ticket_filters)
	{
		$all_ids = array();

		foreach ($ticket_filters as $ticket_filter) {
			$all_ids[$ticket_filter['id']] = $ticket_filter->getResults();
		}

		return $all_ids;
	}


	/**
	 * Get an array of IDs for each filter in a collection
	 *
	 * @param $ticket_filters
	 * @return array
	 */
	public function getAllHoldIdsForFiltersCollection($ticket_filters)
	{
		$all_ids = array();

		foreach ($ticket_filters as $ticket_filter) {
			$searcher = $ticket_filter->getSearcher(array('type' => 'is_hold', 'op' => 'is', 'options' => array('is_hold' => 1)));
			$all_ids[$ticket_filter['id']] = $searcher->getResults();
		}

		return $all_ids;
	}


	/**
	 * @param $ticket_filter
	 * @return
	 */
	public function getIdsFromFilter($ticket_filter)
	{
		$ticket_filter = App::getOrm()->getRepository('DeskPRO:TicketFilter')->getTicketFilterFromVar($ticket_filter);

		$result_ids = $ticket_filter->getResults();

		return $result_ids;
	}


	/**
	 * Get ticket results from a filter
	 *
	 * @param TicketFilter $ticket_filter
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
