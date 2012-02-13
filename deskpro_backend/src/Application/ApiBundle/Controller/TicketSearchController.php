<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;

/**
 * Perform searches or get results from filters.
 */
class TicketSearchController extends AbstractController
{
	/**
	 * Get a map of filters.
	 */
	public function getFilterNamesAction()
	{
		$filters = App::getApi('tickets.filters')->getFiltersForPerson($this->user);

		return $this->renderJson('ApiBundle:TicketSearch:get-filter-names.phpj.json', array(
			'filters' => $filters
		));
	}



	/**
	 * Get counts for all filters
	 */
	public function getFilterCountsAction()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsForPersonFilters($this->user);

		return $this->renderJson('ApiBundle:TicketSearch:get-filter-counts.phpj.json', array(
			'counts' => $all_counts
		));
	}



	/**
	 * Execute a filter and return results.
	 *
	 * @param int $filter_id
	 */
	public function getFilterResultsAction($filter_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$filter = App::getApi('tickets.filters')->getFilterFromId($filter_id);
		$num_results = $filter->getResultsCount();
		$num_pages = ceil($num_results / $per_page);

		$tickets = App::getApi('tickets.filters')->getTicketsFromFilter($filter_id, $page, $per_page);

		return $this->renderJson('ApiBundle:TicketSearch:get-filter-results.phpj.json', array(
			'num_tickets' => $num_results,
			'num_pages' => $num_pages,
			'per_page' => $per_page,
			'cur_page' => $page,
			'tickets' => $tickets
		));
	}
}
