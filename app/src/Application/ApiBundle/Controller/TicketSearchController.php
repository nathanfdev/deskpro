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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Searcher\TicketSearch;

/**
 * Perform searches or get results from filters.
 */
class TicketSearchController extends AbstractController
{
	public function searchAction()
	{
		$search_map = array(
			'agent_id'        => TicketSearch::TERM_AGENT,
			'agent_team_id'   => TicketSearch::TERM_AGENT_TEAM,
			'category_id'     => TicketSearch::TERM_CATEGORY,
			'department_id'   => TicketSearch::TERM_DEPARTMENT,
			'label'           => TicketSearch::TERM_LABEL,
			'language_id'     => TicketSearch::TERM_LANGUAGE,
			'organization_id' => TicketSearch::TERM_ORGANIZATION,
			'participant'     => TicketSearch::TERM_PARTICIPANT,
			'person_id'       => TicketSearch::TERM_PERSON,
			'priority_id'     => TicketSearch::TERM_PRIORITY,
			'product_id'      => TicketSearch::TERM_PRODUCT,
			'status'          => TicketSearch::TERM_STATUS,
			'subject'         => TicketSearch::TERM_SUBJECT,
			'urgency'         => TicketSearch::TERM_URGENCY,
			'workflow_id'     => TicketSearch::TERM_WORKFLOW,
			'sla_id'          => TicketSearch::TERM_SLA,
			'sla_status'      => TicketSearch::TERM_SLA_STATUS,
			'sla_completed'   => TicketSearch::TERM_SLA_COMPLETED
		);

		$terms = array();

		foreach ($search_map AS $input => $search_key) {
			$value = $this->in->getCleanValueArray($input, 'raw', 'discard');
			if ((is_string($value) && strlen($value) > 0) || (!is_string($value) && $value)) {
				$terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
			}
		}

		foreach ($this->container->getSystemService('ticket_fields_manager')->getFields() as $field) {
			if ($this->in->checkIsset("field." . $field->getId())) {
				$in_val = $this->in->getString('field.'.$field->getId());
				if ($in_val) {
					$terms[] = array('type' => 'ticket_field[' . $field->getId() . ']', 'op' => 'is', 'options' => array('value' => $in_val));
				}
			}
		}

		if ($this->in->getString('query')) {
			$terms[] = array('type' => 'text', 'op' => 'is', 'options' => array('query' => $this->in->getString('query')));
		}

		if ($this->in->checkIsset('order')) {
			$order_by = $this->in->getString('order');
		} else {
			$order_by = $this->person->getPref('agent.ui.ticket-basic-order-by.general');
			if (!$order_by) {
				$order_by = 'ticket.date_created:desc';
			}
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		$result_cache = $this->getApiSearchResult('ticket', $terms, $extra, $this->in->getUint('cache_id'), new \Application\DeskPRO\Searcher\TicketSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$helper = \Application\AgentBundle\Controller\Helper\TicketResults::newFromResultCache($this, $result_cache);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => $helper->getCount(),
			'cache_id' => $result_cache->id,
			'tickets' => $this->getApiData($helper->getTicketsForPage($page, $per_page))
		));
	}

	/**
	 * Get a map of filters.
	 */
	public function getFiltersAction()
	{
		$filters = $this->_getFiltersApi()->getFiltersForPerson($this->person);

		return $this->createApiResponse(array('filters' => $this->getApiData($filters)));
	}



	/**
	 * Execute a filter and return results.
	 *
	 * @param int $filter_id
	 */
	public function getFilterAction($filter_id)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$filter = $this->_getFiltersApi()->getFilterFromId($filter_id);
		$total = $filter->getResultsCount();

		$tickets = $this->_getFiltersApi()->getTicketsFromFilter($filter_id, $page, $per_page);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => $total,
			'tickets' => $this->getApiData($tickets)
		));
	}

	/**
	 * @return \Application\DeskPRO\Tickets\Filters
	 */
	protected function _getFiltersApi()
	{
		return App::getApi('tickets.filters');
	}

}
