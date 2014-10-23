<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Orb\Util\Arrays;
use Orb\Util\Numbers;

/**
 * Perform searches or get results from filters.
 *
 * @SWG\Resource(
 * 	resourcePath="/tickets",
 * 	description="Operations about Tickets",
 * 	basePath="/api"
 * )
 */
class TicketSearchController extends AbstractController
{
	/**
	 * @SWG\Api(
	 * 	path="/tickets",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Search for Tickets matching criteria",
	 * 		notes="All constraints are optional. Multiple constraints are AND'd together; multiple values for a single constraint are OR'd.",
	 *		type="array",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="agent_id[]",
	 *				description="Requires ticket to be assigned to the specified agent ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="agent_team_id[]",
	 *				description="Requires ticket to be assigned to an agent in the specific agent team ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="category_id[]",
	 *				description="Requires ticket to be in the specified category ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="department_id[]",
	 *				description="Requires ticket to be in the specified department ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="field[#][]",
	 *				description="Requires ticket custom field to have the specified value in the listed field.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label[]",
	 *				description="Requires ticket to be have the specified label.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="language_id[]",
	 *				description="Requires ticket to be in the specified language ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="organization_id[]",
	 *				description="Requires ticket to be for the specified organization ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="participant[]",
	 *				description="Requires ticket to be followed by or copied to the specified person ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="person_id[]",
	 *				description="Requires ticket to be created by the specified person ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="priority_id[]",
	 *				description="Requires ticket to be have the specified priority ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="product_id[]",
	 *				description="Requires ticket to be for the specified product ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="query",
	 *				description="Requires ticket to contain the specified text within it.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status[]",
	 *				description="Requires ticket to be in the specified status. Possible values are awaiting_user, awaiting_agent, archived, hidden, resolved.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="sla_completed",
	 *				description="If specified, requires the ticket to have the SLA requirement completed (1) or incomplete (0).",
	 *				paramType="query",
	 *				required=false,
	 *				type="boolean"
	 *			),
	 *			@SWG\Parameter(
	 *				name="sla_id[]",
	 *				description="Requires ticket to be have the specified SLA.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="sla_status[]",
	 *				description="Requires ticket to be have the specified SLA status. Possible values: ok, warning, fail.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="subject[]",
	 *				description="Requires ticket subject to match the string provided.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="urgency[]",
	 *				description="Requires ticket to be have the specified urgency (1-10).",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="workflow_id[]",
	 *				description="Requires ticket to be have the specified workflow ID.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="id_min",
	 *				description="Specify min ticket ID (since build 315)",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="id_max",
	 *				description="Specify max ticket ID (since build 315)",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="ref",
	 *				description="Specify a ref or the beginning characters of a ref (since build 315)",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_created",
	 *				description="Constrains results based on the date the ticket was created.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_resolved",
	 *				description="Constrains results based on the date the ticket was first resolved. Unresolved tickets are not included.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_archived",
	 *				description="Constrains results based on the date the ticket was first archived. Unarchived tickets are not included.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_status",
	 *				description="Constrains results based on the date of the last status change.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_last_agent_reply",
	 *				description="Constrains results based on the date of the last agent reply. Tickets without an agent reply are not included.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_last_user_reply",
	 *				description="Constrains results based on the date of the last user reply.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_last_reply",
	 *				description="Constrains results based on the date of the last reply (either a user or agent reply, whichever was latest).",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="order",
	 *				description="Order of the results. Defaults to accessing person's preference or ticket.date_created:desc.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="cache_id",
	 *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="page",
	 *				description="The page number of the results to fetch.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
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
			'sla_completed'   => TicketSearch::TERM_SLA_COMPLETED,
		);

		$date_search_map = array(
			'date_created'          => TicketSearch::TERM_DATE_CREATED,
			'date_resolved'         => TicketSearch::TERM_DATE_RESOLVED,
			'date_archived'         => TicketSearch::TERM_DATE_ARCHIVED,
			'date_status'           => TicketSearch::TERM_DATE_STATUS,
			'date_last_agent_reply' => TicketSearch::TERM_DATE_LAST_AGENT_REPLY,
			'date_last_user_reply'  => TicketSearch::TERM_DATE_LAST_USER_REPLY,
			'date_last_reply'       => TicketSearch::TERM_DATE_LAST_REPLY,
		);

		$terms = array();

		foreach ($search_map AS $input => $search_key) {
			$value = $this->in->getCleanValueArray($input, 'raw', 'discard');
			if ((is_string($value) && strlen($value) > 0) || (!is_string($value) && $value)) {
				$op = $this->in->getString($search_key . '_op') ?: 'contains';
				$terms[] = array('type' => $search_key, 'op' => $op, 'options' => $value);
			}
		}

		$id_min = $this->in->getUint('id_min');
		$id_max = $this->in->getUint('id_max');
		if ($id_min || $id_max) {
			$terms[] = array('type' => 'id', 'op' => 'between', 'options' => array($id_min, $id_max));
		} else if ($id = $this->in->getUint('id')) {
			$terms[] = array('type' => 'id', 'op' => 'is', 'options' => array($id));
		}

		if ($ref = $this->in->getString('ref')) {
			$op = $this->in->getString('ref_op') ?: 'contains';
			$terms[] = array('type' => 'ref', 'op' => $op, 'options' => array('ref' => $ref));
		}

		$proc_date_input = function($date_input) {
			$date = null;
			if (Numbers::isTimestamp($date_input)) {
				try {
					$date = new \DateTime("@$date_input");
				} catch (\Exception $e) {
					$date = null;
				}
			}

			if (!$date) {
				try {
					$date = \DateTime::createFromFormat(\DateTime::ISO8601, $date_input);
				} catch (\Exception $e) {
					$date = null;
				}
			}

			if (!$date) {
				try {
					$date = \DateTime::createFromFormat('Y-m-d H:i:s', $date_input, new \DateTimeZone('UTC'));
				} catch (\Exception $e) {
					$date = null;
				}
			}

			if (!$date) {
				try {
					$date = \DateTime::createFromFormat('Y-m-d', $date_input, new \DateTimeZone('UTC'));
					$date->setTime(0,0,0);
				} catch (\Exception $e) {
					$date = null;
				}
			}

			return $date;
		};

		foreach ($date_search_map as $input => $search_key) {
			$raw = $this->in->getString($input);
			if (!$raw) {
				continue;
			}

			$date1 = null;
			$date2 = null;

			if (strpos($raw, '/') !== false) {
				$op = 'between';
				list ($date1_input, $date2_input) = explode('/', $raw, 2);

				$date1 = $proc_date_input($date1_input);
				$date2 = $proc_date_input($date2_input);

				if (!$date1 || !$date2) {
					return $this->createApiErrorResponse('invalid_term', "$input includes a bad date range: $raw. Expected format: date1/date2 where the dates are unix timestamps or ISO 8601");
				}

				$options = array('date1' => $date1, 'date2' => $date2);

			} else {
				$op_sym = $raw[0];
				$raw = substr($raw, 1);

				if ($op_sym == '<' || $op_sym == '<=') {
					$op = 'lt';
				} else if ($op_sym == '>' || $op_sym == '>=') {
					$op = 'gt';
				} else {
					return $this->createApiErrorResponse('invalid_term', "$input includes a bad operator: $op_sym. Expected '<' or '>'");
				}

				$date1 = $proc_date_input($raw);
				if (!$date1) {
					return $this->createApiErrorResponse('invalid_term', "$input includes a bad date: $raw. Expected format is a unix timestamp or ISO 8601");
				}

				$options = array('date1' => $date1);
			}

			$terms[] = array('type' => $search_key, 'op' => $op, 'options' => $options);
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
			$terms[] = array('type' => 'ticket_message', 'op' => 'is', 'options' => array('ticket_message' => $this->in->getString('query')));
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

		$per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

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
		$data = array('filters' => $this->getApiData($filters));

		if ($this->in->getBool('with_counts')) {
			$all_counts = App::getApi('tickets.filters')->getAllCountsForFiltersCollection($filters);
			$all_counts = Arrays::castToType($all_counts, 'int', 'int');

			$data['counts'] = $all_counts;
		}

		return $this->createApiResponse($data);
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

		$per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

		$filter = $this->_getFiltersApi()->getFilterFromId($filter_id);
		if (!$filter) {
			throw $this->createNotFoundException();
		}

		$total = $filter->getResultsCount();

		$tickets = $this->_getFiltersApi()->getTicketsFromFilter($filter_id, $page, $per_page);

		return $this->createApiResponse(array(
			'page'     => $page,
			'per_page' => $per_page,
			'total'    => $total,
			'tickets'  => $this->getApiData($tickets),
			'filter'   => $filter->toApiData(true)
		));
	}

	/**
	 * Get array of filters and counts
	 */
	public function getFilterCountsAction()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsCustomFilters($this->person);
		$all_counts = Arrays::castToType($all_counts, 'int', 'int');

		return $this->createApiResponse(array(
			'filter_counts' => $all_counts
		));
	}

	/**
	 * @return \Application\DeskPRO\Tickets\Filters
	 */
	protected function _getFiltersApi()
	{
		return App::getApi('tickets.filters');
	}


	public function getQuickStatsAction()
	{
		$stats = array();
		$today = $this->person->getDateTime();
		$today->setTime(0,0,0);
		$today->setTimezone(\Orb\Util\Dates::tzUtc());
		$today = $today->format('Y-m-d H:i:s');

		$stats['created_today']  = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE date_created > ?", array($today));
		$stats['resolved_today'] = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE date_resolved > ?", array($today));
		$stats['awaiting_agent'] = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets WHERE status = 'awaiting_agent'");

		return $this->createApiResponse($stats);
	}
}
