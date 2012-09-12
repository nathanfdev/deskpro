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

/**
 * Perform searches or get results from filters.
 */
class TicketSearchController extends AbstractController
{
	public function searchAction()
	{
		$set_terms_map = array(
			'department'    => array('op' => 'contains', 'options' => array()),
			'status'        => array('op' => 'contains', 'options' => array()),
			'agent'         => array('op' => 'contains', 'options' => array()),
			'agent_team'    => array('op' => 'contains', 'options' => array()),
			'participant'   => array('op' => 'contains', 'options' => array()),
			'category'      => array('op' => 'contains', 'options' => array()),
			'product'       => array('op' => 'contains', 'options' => array()),
			'priority'      => array('op' => 'contains', 'options' => array()),
			'workflow'      => array('op' => 'contains', 'options' => array()),
			'organization'  => array('op' => 'contains', 'options' => array()),
			'language'      => array('op' => 'contains', 'options' => array()),
		);

		$terms = array();

		foreach ($set_terms_map as $name => $info) {
			if ($this->in->checkIsset($name)) {
				$in_val = $this->in->getCleanValueArray($name, 'raw', 'discard');
				if ($in_val) {
					$new_term = $info;
					$new_term['options'] = $in_val;
					\Orb\Util\Arrays::unshiftAssoc($new_term, 'type', $name);
					$terms[] = $new_term;
				}
			}
		}

		foreach ($this->container->getSystemService('ticket_fields_manager')->getFields() as $field) {
			if ($this->in->checkIsset("fields." . $field->getId())) {
				$in_val = $this->in->getString('fields.'.$field->getId());
				if ($in_val) {
					$terms[] = array('type' => 'ticket_field[' . $field->getId() . ']', 'op' => 'is', 'options' => array('value' => $in_val));
				}
			}
		}

		if ($this->in->getString('query')) {
			$terms[] = array('type' => 'text', 'op' => 'is', 'options' => array('query' => $this->in->getString('query')));
		}

		$order_by = $this->person->getPref('agent.ui.ticket-basic-order-by.general');

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		if ($this->in->checkIsset('cache')) {
			$cache = $this->in->getUint('cache');
		} else {
			$cache = 3600;
		}

		$cache_date = new \DateTime('-' . $cache . ' seconds', new \DateTimeZone('UTC'));

		$query_params = array(
			$this->person->id,
			serialize($terms),
			serialize($extra),
			$cache_date->format('Y-m-d H:i:s')
		);

		$id = $this->db->fetchColumn('
			SELECT id
			FROM result_cache
			WHERE person_id = ? AND criteria = ? AND extra = ? AND date_created > ?
			ORDER BY date_created DESC
			LIMIT 1
		', $query_params);

		if ($id) {
			$result_cache = $this->em->createQuery('
				SELECT r
				FROM DeskPRO:ResultCache r
				WHERE r.id = ?0
			')->setParameters(array($id))->getOneOrNullResult();
		} else {
			$result_cache = null;
		}

		if (!$result_cache) {
			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			$searcher->setPerson($this->person);
			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			}

			foreach ($terms as $term) {
				$searcher->addTerm($term['type'], $term['op'], $term['options']);
			}

			$results = $searcher->getMatches();

			$result_cache = new \Application\DeskPRO\Entity\ResultCache();
			$result_cache->person = $this->person;
			$result_cache->results = $results;
			$result_cache->criteria = $terms;
			$result_cache->num_results = count($results);
			$result_cache->setExtraData('order_by', $order_by);

			$this->em->persist($result_cache);
			$this->em->flush();
		}

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$helper = \Application\AgentBundle\Controller\Helper\TicketResults::newFromResultCache($this, $result_cache);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => $helper->getCount(),
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
