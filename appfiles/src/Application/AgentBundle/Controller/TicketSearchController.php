<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Entity\TicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

use Application\DeskPRO\UI\RuleBuilder;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('AgentBundle:TicketSearch:list-blank.html.twig');
	}

	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# Filters
		#------------------------------

		$all_filters = App::getApi('tickets.filters')->getFiltersForPerson($this->person);

		$order = $this->person->getPref('agent.ui.ticket-filters-order');
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

		if (!$this->person->getHasTeams()) {
			unset($sys_filters['agent_team']);
			unset($sys_filters_unordered['agent_team_w_hold']);
		}

		$filter_id_matches = App::getApi('tickets.filters')->getAllIdsForFiltersCollection($all_filters);

		// Summary of terms for all filters
		$filters_summary = array();
		foreach ($all_filters as $filter) {
			$searcher = $filter->getSearcher();
			$filters_summary[$filter['id']] = $searcher->getSummary();
		}

		#------------------------------
		# Flags / flag order
		#------------------------------

		$flags = array('blue','green','orange','pink','purple','red','yellow');
		$flags = array_combine($flags, $flags);

		$order = $this->person->getPref('agent.ui.ticket-flag-order');
		if ($order) {
			$flags_unordered = $flags;
			$flags = array();

			foreach ($order as $id) {
				if (isset($flags_unordered[$id])) {
					$flags[] = $flags_unordered[$id];
					unset($flags_unordered[$id]);
				}
			}

			if (count($flags_unordered)) {
				foreach ($flags_unordered as $id) {
					$flags[] = $id;
				}
			}
		}
		$flags = array_values($flags);

		#------------------------------
		# Misc
		#------------------------------

		$archive_counts = $this->em->getRepository('DeskPRO:Ticket')->getArchiveCounts();

		$data['section_html'] = $this->renderView('AgentBundle:TicketSearch:window-section.html.twig', array(
			'sys_filters' => $sys_filters,
			'sys_filters_hold' => $sys_filters_hold,
			'filter_id_matches' => $filter_id_matches,
			'filters_summary' => $filters_summary,
			'custom_filters' => $custom_filters,
			'flags' => $flags,
			'archive_counts' => $archive_counts,
		));

		$data['filter_id_matches'] = $filter_id_matches;

		return $this->createJsonResponse($data);
	}


	/**
	 * Render a new pageset.
	 *
	 * The client has a full list of IDs from a search. When he wants the next page,
	 * he sends a set of new IDs in the result set and we return the HTML to inject
	 * into his view.
	 *
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function getTicketPageAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('result_ids', 'uint', 'discard');
		$ticket_ids = Arrays::removeFalsey($ticket_ids);
		$ticket_ids = array_unique($ticket_ids);

		$tickets = $this->em->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids, $this->person);
		$tickets = Arrays::orderIdArray($ticket_ids, $tickets);

		$display_fields = $this->in->getCleanValueArray('display_fields', 'str_simple', 'discard');
		if (!$display_fields) {
			$display_fields = array('department', 'agent', 'agent_team');
		}

		// Accept changes to apply for previewing
		// - We just apply the changes but dont save them, they'll be
		//   properly displayed in the listing.
		$actions = $this->in->getCleanValueArray('actions', 'raw', 'string');
		$changed_fields = array();

		if ($actions && $tickets) {
			$factory = new ActionsFactory();
			$collection = new ActionsCollection();

			foreach ($actions as $name => $opt) {
				$action = $factory->createFromForm($name, $opt);
				$collection->add($action);

				$display_fields[] = $name;
			}

			foreach ($tickets as $t) {
				$ticket_changes = $collection->getApplyActions($t, $this->person);
				$collection->apply($t, $this->person);

				if ($ticket_changes) {
					$ticket_changed_fields = array();
					foreach ($ticket_changes as $change) {
						$ticket_changed_fields[$change['action']] = true;
						$changed_fields[$t['id']] = $ticket_changed_fields;
					}
				}
			}

			$display_fields = array_unique($display_fields);
		}

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$person_field_defs = App::getApi('custom_fields.people')->getEnabledFields();

		$tpl = 'part-results-simple-ext.html.twig';
		if ($this->in->getString('view_type') == 'list') {
			$tpl = 'part-results-simple-ext.html.twig';
		}

		return $this->render("AgentBundle:TicketSearch:$tpl", array(
			'tickets'           => $tickets,
			'display_fields'    => $display_fields,
			'ticket_field_defs' => $ticket_field_defs,
			'person_field_defs' => $person_field_defs,
			'changed_fields'    => $changed_fields,
		));
	}


	/**
	 * We get in an array of ticket ID batches. Each batch is identified by some ID,
	 * usually a filter ID from Tickets.js section.
	 *
	 * array(
	 *     'batchId' => array('grouping' => 'xxx', 'ticket_ids' => array(x,x,x))
	 * )
	 *
	 * We group the batches, and return titles, search URL's, and counts for the batch
	 * using the same batch ID:
	 *
	 * array(
	 *     'batchId' => 'subgroup_html'
	 * )
	 */
	public function groupTicketsAction()
	{
		$ticket_batches = $this->in->getArrayValue('batches');
		$batches = array();

		foreach ($ticket_batches as $batch_id => $ticket_batch) {
			if (!$ticket_batch || empty($ticket_batch['ticket_ids'])) {
				$batches[$batch_id] = '';
				continue;
			}

			$grouper = new \Application\DeskPRO\Tickets\GroupingCounter();
			$grouper->setGrouping($ticket_batch['grouping']);
			$grouper->setMode('specify', $ticket_batch['ticket_ids']);

			$grouped_info = $grouper->getDisplayArray();
			//print_r($grouped_info);exit;
			$batches[$batch_id] = $this->renderView('AgentBundle:TicketSearch:window-filter-groupresult.html.twig', array(
				'grouped_info' => $grouped_info,
			));
		}

		return $this->createJsonResponse($batches);
	}


	public function getFlaggedSectionDataAction()
	{
		$data = array();
		$data['flag_counts'] = App::getEntityRepository('DeskPRO:TicketFlagged')->getCountsForPerson($this->person);

		return $this->createJsonResponse($data);
	}

	public function filtersPaneAction()
	{
		$filters = App::getApi('tickets.filters')->getFiltersForPerson($this->person);

		$order = $this->person->getPref('agent.ui.ticket-filters-order');
		if ($order) {
			$filters_unordered = $filters;
			$filters = array();

			foreach ($order as $id) {
				if (isset($filters_unordered[$id])) {
					$filters[$id] = $filters_unordered[$id];
					unset($filters_unordered[$id]);
				}
			}

			if (count($filters_unordered)) {
				foreach ($filters_unordered as $id => $q) {
					$filters[$id] = $q;
				}
			}
		}

		return $this->render('AgentBundle:TicketSearch:pane-filters.html.twig', array(
			'filters' => $filters
		));
	}

	public function runFilterAction($filter_id)
	{
		$filter = App::getEntityRepository('DeskPRO:TicketFilter')->find($filter_id);

		$searcher = $filter->getSearcher();
		$searcher->setPerson($this->person);

		$order_by = $this->in->getString('order_by');
		if (!$order_by) {
			$order_by = $this->person->getPref('agent.ui.ticket-filter-order-by.' . $filter['id']);
		}
		if (!$order_by AND $filter['order_by']) {
			$order_by = $filter['order_by'];
		}
		if ($order_by) {
			$searcher->setOrderByCode($order_by);
		}

		$set_group_term = null;
		$set_group_option = null;
		if ($this->in->getString('set_group_term')) {
			$set_group_term = $this->in->getString('set_group_term');
			$set_group_option = $this->in->getString('set_group_option');

			$term = \Application\DeskPRO\Tickets\GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
			if ($term) {
				$searcher->addTerm($term['type'], $term['op'], $term['options']);
			}
		}

		$results = $searcher->getMatches();
		$results = Arrays::castToType($results, 'integer');

		$helper = new Helper\TicketResults($this);
		$helper->setTicketIds($results);

		if ($this->in->getString('group_by')) {
			$helper->setGroupField($this->in->getString('group_by'));
		} elseif ($filter['group_by']) {
			$helper->setGroupField($filter['group_by']);
		}

		// Or if the user has their own
		$group_by = $this->person->getPref('agent.ui.ticket-filter-group-by.' . $filter['id']);
		if ($group_by) {
			$helper->setGroupField($group_by);
		}

		$vars = array(
			'filter' => $filter,
			'filter_id' => $filter['id'],
			'order_by_summary' => $searcher->getOrderBySummary(),
			'terms_summary' => $searcher->getSummary(),
			'set_group_term' => $set_group_term,
			'set_group_option' => $set_group_option,
			'ticket_ids' => $results
		);

		$pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.' . $filter['id']);
		if ($pref_display_fields) {
			$vars['display_fields'] = $pref_display_fields;
		} else {
			// Default display fields based on the filter
			$vars['display_fields'] = $this->_suggestedDisplayFields($filter->getSearcher());
		}

		$vars['filter'] = $filter;

		$search_form = array(
			'terms' => $filter['terms'],
			'order_by' => $filter['order_by'],
		);
		$vars['search_form'] = $search_form;

		return $this->_getResponseForTickets('filter', $filter['id'], $helper, $vars);
	}

	public function getFilterSummaryAction($filter_id)
	{
		$filter = App::getEntityRepository('DeskPRO:TicketFilter')->find($filter_id);
		$searcher = $filter->getSearcher();

		return $this->render('AgentBundle:TicketSearch:filter-tip-summary.html.twig', array(
			'title' => $filter['title'],
			'terms_summary' => $searcher->getSummary()
		));
	}

	public function runNamedFilterAction($filter_name)
	{
		$filter = App::getEntityRepository('DeskPRO:TicketFilter')->findOneBy(array('sys_name' => $filter_name));
		return $this->runFilterAction($filter['id']);
	}

	protected function _getResponseForTickets($type, $type_id, $results_helper, array $vars = array())
	{
		$view_type = $this->in->getString('view_type');
		if (!$view_type OR !in_array($view_type, array('list', 'simple', 'simple-ext'))) {
			$view_type = 'simple-ext';
		}

		$is_partial = false;
		$tpl = 'AgentBundle:TicketSearch:'.$type.'-results-'.$view_type.'.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:TicketSearch:part-results-'.$view_type.'.html.twig';
		}

		$per_page = 50;
		if ($view_type == 'list') {
			$per_page = 20;
		}

		#------------------------------
		# Get the tickets to show
		#------------------------------

		$grouped_info = null;
		if (!$is_partial AND $results_helper->isGroupable()) {
			$grouped_info = $results_helper->getGroupDisplayInfo();
		}

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		if (!$this->in->checkIsset('grouping_option')) {
			// User looking at all results
			$is_grouping = false;
			$grouping_option = 'DP_NOT_SET';
			$tickets = $results_helper->getTicketsForPage($page, $per_page);
		} else {
			// User looking at just a group of results
			$is_grouping = true;
			$grouping_option = $this->in->getString('grouping_option');
			$tickets = $results_helper->getGroupedTicketsForPage($this->in->getString('grouping_option'), $page, $per_page);
		}

		#------------------------------
		# Send results
		#------------------------------

		if (!count($tickets) && $is_partial) {
			return $this->createJsonResponse(array('no_more_results' => true));
		}

		$flagged_tickets = App::getEntityRepository('DeskPRO:TicketFlagged')->getFlagsForTickets($tickets, $this->person);

		if (empty($vars['display_fields'])) {
			$vars['display_fields'] = array('date_created', 'department');
		}

		$macros = null;
		$ticket_options = null;
		if (!$is_partial) {
			$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);
			$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
			$ticket_options['custom_ticket_fields'] = $custom_fields;

			// People stuff
			$ticket_options['people_organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
			$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
			$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);
		}

		// ticket and person defs for columns
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$person_field_defs = App::getApi('custom_fields.people')->getEnabledFields();

		$vars['display_fields'] = array_unique($vars['display_fields']);

		$pageinfo = Numbers::getPaginationPages($results_helper->getCount(), $page, $per_page);

		$order_by = $results_helper;

		$vars = array_merge($vars, array(
			'type'               => $type,
			'type_id'            => $type_id,
			'tickets'            => $tickets,
			'count'              => $results_helper->getCount(),
			'flagged_tickets'    => $flagged_tickets,
			'ticket_options'     => $ticket_options,
			'page'               => $page,
			'pageinfo'           => $pageinfo,
			'per_page'           => $per_page,
			'macros'             => $macros,
			'show_flag'          => true,
			'grouped_info'       => $grouped_info,
			'group_by'           => $results_helper->getGroupField(),
			'grouping_option'    => $grouping_option,
			'grouping_summary'   => $results_helper->getGroupingSummary(),
			'is_grouped_result'  => $is_grouping,
			'ticket_field_defs'  => $ticket_field_defs,
			'person_field_defs'  => $person_field_defs,
			'load_first'         => $this->in->getBool('load_first')
		));

		$html = $this->renderView($tpl, $vars);

		if ($is_partial) {
			return $this->createJsonResponse(array(
				'html'              => $html,
				'page'              => $page,
				'is_grouped_result' => $is_grouping,
			));
		} else {
			return $this->createResponse($html);
		}
	}

	public function getSingleTicketRowAction($filter_id)
	{
		$filter = App::getEntityRepository('DeskPRO:TicketFilter')->find($filter_id);

		$ticket_id = $this->in->getUint('ticket_id');
		$ticket = App::findEntity('DeskPRO:Ticket', $ticket_id);

		$vars = array(
			'page' => -1,
			'tickets' => array($ticket),
			'filter' => $filter,
		);

		$view_type = $this->in->getString('view_type');
		if (!$view_type OR !in_array($view_type, array('list', 'simple', 'simple-ext'))) {
			$view_type = 'simple-ext';
		}

		$pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.' . $filter['id']);
		if ($pref_display_fields) {
			$vars['display_fields'] = $pref_display_fields;
		} else {
			// Default display fields based on the filter
			$vars['display_fields'] = $this->_suggestedDisplayFields($filter->getSearcher());
		}

		$is_partial = true;
		$tpl = 'AgentBundle:TicketSearch:part-results-'.$view_type.'.html.twig';

		return $this->render($tpl, $vars);
	}

	protected function _suggestedDisplayFields(TicketSearch $searcher)
	{
		$display_fields = array('department', 'agent', 'agent_team');

		$specific_fields = $searcher->getSpecificFields();

		$max = 5;
		foreach ($searcher->getTermFields() as $term) {
			if (!in_array($term, $specific_fields)) {
				$display_fields[] = $term;
			}

			$display_fields = array_unique($display_fields);
			if (count($display_fields) >= $max) {
				break;
			}
		}

		return $display_fields;
	}

	############################################################################
	# search (natural language search)
	############################################################################

	public function searchQueryAction()
	{
		$q = $this->in->getString('q');

		$is_search = false;
		$results = false;

		if ($q) {
			$searcher = new TicketSearcher(App::get('deskpro.elastica.manager'), $this->person);

			$is_search = true;
			$results = $searcher->search($q);
		}

		return $this->render('AgentBundle:TicketSearch:search-query-results.html.twig', array(
			'is_search' => $is_search,
			'results'   => $results,
			'query' => $q
		));
	}

	############################################################################
	# deleted-list
	############################################################################

	// TODO handling if search results with the cache etc should be refactored
	// theres dupe code, particularly around updating results based on new order

	/**
	 * Listing of soft-deleted tickets
	 */
	public function deletedListAction()
	{
		$result_cache = false;
		if ($this->in->getUint('cache_id')) {
			$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($this->in->getUint('cache_id'));
			if ($result_cache['person_id'] != $this->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running it for the first time
		#------------------------------

		if (!$result_cache) {
			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			$searcher->addTerm('deleted', 'is', 1);

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $searcher->getTerms(), 'order_by' => '', 'group_by' => '');
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			// Default display fields based on our search
			$result_cache->setExtraData('display_fields', array('deleted_reason'));

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Re-do search if we changed order
		#------------------------------

		// Prefs are saved into extra[]. Of order_by doesn't match
		// the order_by in criteria, that means the user changed it
		// and we have to re-do the search

		if (!empty($result_cache['extra']['order_by']) AND $result_cache['extra']['order_by'] != $result_cache['criteria']['order_by']) {
			$criteria = $result_cache['criteria'];
			$criteria['order_by'] = $result_cache['extra']['order_by'];

			$result_cache['criteria'] = $criteria;

			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			$searcher->setTerms($result_cache['criteria']['terms']);
			$searcher->setOrderByCode($result_cache['criteria']['order_by']);

			$results = $searcher->getMatches();
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Serve results
		#------------------------------

		$results_helper = Helper\TicketResults::newFromResultCache($this, $result_cache);

		// Fetch deleted ticket info
		$deleted_tickets = null;
		if ($result_cache['results']) {
			$deleted_tickets = App::getOrm()->createQuery("
				SELECT d
				FROM DeskPRO:TicketDeleted d INDEX BY d.ticket_id
				LEFT JOIN d.by_person p
				WHERE d.ticket_id IN (" . implode(',', $result_cache['results']) . ")
			");
		}

		$vars = array(
			'cache' => $result_cache,
			'cache_id' => $result_cache['id'],
			'deleted_tickets' => $deleted_tickets,
			'show_deleted_reason' => true,
			'disable_actions' => true
		);

		$pref_name = 'agent.ui.ticket-filter-display-fields.' . $result_cache['id'];
		if (!empty($result_cache['extra'][$pref_name])) {
			$vars['display_fields'] = $result_cache['extra'][$pref_name];
		}

		$vars['page_title'] = 'Deleted';

		return $this->_getResponseForTickets('deleted-list', $result_cache['id'], $results_helper, $vars);
	}

	############################################################################
	# find-pane
	############################################################################

	public function newCustomFilterAction()
	{
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$ticket_options['custom_ticket_fields'] = $custom_fields;

		// People stuff
		$ticket_options['people_organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

		return $this->render('AgentBundle:TicketSearch:custom-filter-newsearch.html.twig', array(
			'ticket_options' => $ticket_options,
		));
	}

	public function runCustomFilterAction()
	{
		$result_cache = false;
		if ($this->in->getUint('cache_id')) {
			$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($this->in->getUint('cache_id'));
			if ($result_cache['person_id'] != $this->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running it for the first time
		#------------------------------

		$is_new_recentsearch = false;
		$recent_search_id = 0;

		if (!$result_cache) {

			$recent_searches = $this->person->getPref('agent.recent-searches');

			$term_rules = RuleBuilder::newTermsBuilder();


			$order_by = null;
			$terms = null;
			if ($recent_search_id = $this->in->getString('recent_search_id')) {
				if (isset($recent_searches[$recent_search_id])) {
					$terms = $recent_searches[$recent_search_id]['terms'];
					$order_by = $recent_searches[$recent_search_id]['order_by'];
				} else {
					$recent_search_id = null;
				}
			} else {
				$recent_search_id = null;
			}

			if (!$terms) {
				$terms = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));
			}

			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();

			$user_searcher = new \Application\DeskPRO\Searcher\PersonSearch();
			$has_user_terms = false;

			foreach ($terms as $term) {
				if (strpos($term['type'], 'person_') === 0) {
					$user_searcher->addTerm($term['type'], $term['op'], $term['options']);
					$has_user_terms = true;
				} else {
					$searcher->addTerm($term['type'], $term['op'], $term['options']);
				}
			}

			if ($has_user_terms) {
				$searcher->setPersonSearch($user_searcher);
			}

			if (!$order_by) {
				$order_by = $this->in->getString('filter.order_by');
			}
			//$group_by = $this->in->getString('filter.group_by');
			$group_by = '';

			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			}

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $terms, 'order_by' => $order_by, 'group_by' => $group_by);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			// Default display fields based on our search
			$result_cache->setExtraData('display_fields', $this->_suggestedDisplayFields($searcher));

			// If this isnt already a recent search, add it to recent searches now
			if (!$recent_search_id) {
				if (!$recent_searches) {
					$recent_searches = array();
				}
				if (count($recent_searches) >= 5) {
					$recent_searches = array_slice($recent_searches, 0, 4, true);
				}

				$recent_search_id = uniqid('search_').mt_rand(1000,9999);
				Arrays::unshiftAssoc($recent_searches, $recent_search_id, array(
					'id' => $recent_search_id,
					'terms' => $terms,
					'order_by' => $order_by,
					'summary' => $searcher->getSummary()
				));

				$this->person->setPreference('agent.recent-searches', $recent_searches);
				$is_new_recentsearch = true;
			}

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Re-do search if we changed order
		#------------------------------

		// Prefs are saved into extra[]. Of order_by doesn't match
		// the order_by in criteria, that means the user changed it
		// and we have to re-do the search

		if (!empty($result_cache['extra']['order_by']) AND $result_cache['extra']['order_by'] != $result_cache['criteria']['order_by']) {
			$criteria = $result_cache['criteria'];
			$criteria['order_by'] = $result_cache['extra']['order_by'];

			$result_cache['criteria'] = $criteria;

			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			$searcher->setTerms($result_cache['criteria']['terms']);
			$searcher->setOrderByCode($result_cache['criteria']['order_by']);

			$results = $searcher->getMatches();
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);
			$result_cache->setExtraData('terms_summary', $searcher->getSummary());

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Serve results
		#------------------------------

		$results_helper = Helper\TicketResults::newFromResultCache($this, $result_cache);

		$vars = array(
			'cache'               => $result_cache,
			'cache_id'            => $result_cache['id'],
			'terms_summary'       => $result_cache->getExtraData('terms_summary'),
			'is_new_recentsearch' => $is_new_recentsearch, // this triggers recent search list update
			'recent_search_id'    => $recent_search_id
		);

		$search_form = array(
			'terms' => $result_cache['criteria'],
			'order_by' => !empty($result_cache['extra']['order_by']) ? $result_cache['extra']['order_by'] : ''
		);
		$vars['search_form'] = $search_form;

		if (!empty($result_cache['extra']['display_fields'])) {
			$vars['display_fields'] =$result_cache['extra']['display_fields'];
		}

		$pref_name = 'agent.ui.ticket-filter-display-fields.' . $result_cache['id'];
		if (!empty($result_cache['extra'][$pref_name])) {
			$vars['display_fields'] = $result_cache['extra'][$pref_name];
		}

		if ($this->in->getString('page_title')) {
			$vars['page_title'] = $this->in->getString('page_title');
		}

		// Used in the templates/JS pages to highlight active nav elements
		if ($this->in->getString('view_name')) {
			$vars['view_name'] = $this->in->getString('view_name');
			if ($this->in->getString('view_extra')) {
				$vars['view_extra'] = $this->in->getString('view_extra');
			}
		}

		return $this->_getResponseForTickets('custom-filter', $result_cache['id'], $results_helper, $vars);
	}

	public function getRecentSearchesListAction()
	{
		$recent_searches = $this->person->getPref('agent.recent-searches');

		return $this->render('AgentBundle:TicketSearch:window-recentsearch-list.html.twig', array(
			'recent_searches' => $recent_searches
		));
	}

	############################################################################
	# overview-pane
	############################################################################

	public function overviewPaneAction()
	{
		return $this->render('AgentBundle:TicketSearch:pane-overview.html.twig', array(

		));
	}

	public function overviewNavAction()
	{
		$group1 = $this->in->getString('group1');
		$group2 = $this->in->getString('group2');

		$grouper = new \Application\DeskPRO\Tickets\GroupingCounter();
		$grouper->setGrouping($group1, $group2);
		$grouper->setMode($this->in->getString('mode'), $this->person['id']);

		$mode_crit = '';
		if ($this->in->getString('mode') == 'agent') {
			$mode_crit = "terms[0][type]=agent&terms[0][op]=is&terms[0][options][agent]=-1";
		} elseif ($this->in->getString('mode') == 'agent_team') {
			$mode_crit = "terms[0][type]=agent_team&terms[0][op]=is&terms[0][options][agent_team]=-1";
		} elseif ($this->in->getString('mode') == 'participant') {
			$mode_crit = "terms[0][type]=participant&terms[0][op]=is&terms[0][options][person_id]=".$this->person['id'];
		} elseif ($this->in->getString('mode') == 'unassigned') {
			$mode_crit = "terms[0][type]=agent&terms[0][op]=is&terms[0][options][agent]=0";
		} else {
			// all, no crit
		}

		$results = $grouper->getDisplayArray();

		unset($results['items'][-1]);// TODO 0 is the 'total', we'll use that later in the UI

		// TODO: Need a cleaner way of converting a group into a searchable item
		$group1_nosuf = preg_replace('#_id$#', '', $group1);
		$list_url_group1 = $this->generateUrl('agent_ticketsearch_runcustomfilter') . "?page_title=\$page_title&$mode_crit&terms[5][type]=status&terms[5][op]=is&terms[5][options][status]=open&terms[6][type]=$group1_nosuf&terms[6][op]=is&terms[6][options][$group1_nosuf]=\$group1_id";

		$group2_nosuf = preg_replace('#_id$#', '', $group2);
		$list_url_group2 = $list_url_group1 . "&terms[7][type]=$group2_nosuf&terms[7][op]=is&terms[7][options][$group2_nosuf]=\$group2_id";

		//print_r($results['group1_structure']);

		return $this->render('AgentBundle:TicketSearch:overview-listing.html.twig', array(
			'results' => $results,
			'list_url_group1' => $list_url_group1,
			'list_url_group2' => $list_url_group2,
		));
	}

	############################################################################
	# labels-pane
	############################################################################

	public function labelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('ticket', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:TicketSearch:pane-labels.html.twig', array(
			'cloud' => $cloud
		));
	}

	public function labelsIndexPaneAction()
	{
		$label_lister = new \Application\DeskPRO\Labels\LabelLister('tickets');
		$index = $label_lister->getIndexList();

		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('ticket', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:TicketSearch:pane-labels-index.html.twig', array(
			'labels_index' => $index,
			'labels_cloud' => $cloud,
		));
	}

	############################################################################
	# flagged
	############################################################################

	public function flaggedPaneAction()
	{
		$flags = array('blue','green','orange','pink','purple','red','yellow');
		$flags = array_combine(array_values($flags), $flags);

		$order = $this->person->getPref('agent.ui.ticket-flag-order');
		if ($order) {
			$flags_unordered = $flags;
			$flags = array();

			foreach ($order as $id) {
				if (isset($flags_unordered[$id])) {
					$flags[] = $flags_unordered[$id];
					unset($flags_unordered[$id]);
				}
			}

			if (count($flags_unordered)) {
				foreach ($flags_unordered as $id) {
					$flags[] = $id;
				}
			}
		}
		return $this->render('AgentBundle:TicketSearch:pane-flagged.html.twig', array(
			'flags' => $flags
		));
	}

	public function runFlaggedAction($flag)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.filters')->getTicketsFromFlagged($flag, $this->person, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:flagged-results.html.twig';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:part-results-list.html.twig';
			if (!count($tickets)) {
				return $this->createResponse('');
			}
		}

		return $this->render($tpl, array(
			'flag' => $flag,
			'tickets' => $tickets,
			'page' => $page,
			'display_fields' => array('person', 'agent')
		));
	}


	############################################################################
	# save-result-prefs
	############################################################################

	public function ajaxSaveResultPrefsAction($cache_id)
	{
		$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($cache_id);

		$extra = $result_cache['extra'];

		foreach ($this->in->getCleanValueArray('prefs', 'raw', 'str_simple') as $pref_name => $value)
		{
			// Remove trailing .ID for cleaner case test
			$pref_name = str_replace('.'.$result_cache['id'], '', $pref_name);
			switch ($pref_name) {
				case 'agent.ui.ticket-filter-order-by':
					$pref_name = 'order_by';
					break;
				case 'agent.ui.ticket-filter-display-fields':
					$pref_name = 'display_fields';
					break;
				default:
					throw new \InvalidArgumentException("Invalid preference `$pref_name`");
					break;
			}

			$extra[$pref_name] = $value;
		}

		$result_cache['extra'] = $extra;

		App::getOrm()->persist($result_cache);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => true));
	}


	############################################################################
	# ajax-mass-actions
	############################################################################

	public function ajaxMassActionsAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		$actions = $this->in->getCleanValueArray('actions', 'raw', 'string');

		App::getOrm()->beginTransaction();

		foreach ($tickets as $ticket) {
			$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
			$result = $ticket_edit->applyActions($actions);
			$ticket_edit->save();
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# ajax-release-locks
	############################################################################

	public function ajaxReleaseLocksAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		App::getOrm()->beginTransaction();

		foreach ($tickets as $ticket) {
			$ticket->unlockTicket();

			$lock_cm = new ClientMessage();
			$lock_cm->fromArray(array(
				'channel' => 'agent-notification.tickets.unlocked',
				'data' => array(
					'ticket_id' => $ticket['id'],
					'agent_id' => $ticket['id'],
				),
				'created_by_client' => $this->session->getEntity()->getId(),
			));

			App::getOrm()->persist($ticket);
			App::getOrm()->persist($lock_cm);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true));
	}


	############################################################################
	# ajax-delete-ticket
	############################################################################

	public function ajaxDeleteTicketsAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		$deleted_tickets = array();

		App::getOrm()->beginTransaction();
		foreach ($tickets as $ticket) {
			$deleted_tickets[] = $ticket['id'];
			App::getOrm()->remove($ticket);
		}
		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true, 'deleted_tickets' => $deleted_tickets));
	}



	############################################################################
	# ajax-get-macro-actions
	############################################################################

	public function ajaxGetMacroAction()
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		$tickets = null;
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		if ($ticket_ids) {
			$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);
		}

		$actions = $macro->getActionsArrayForCollection($tickets);

		$data = array();
		$data['raw_actions'] = array();
		$data['ticket_actions'] = $actions;

		$raw_actions = $macro->getActionsArray();
		if (!empty($raw_actions['new_reply'])) {
			$data['raw_actions']['new_reply'] = $raw_actions['new_reply'];
		}

		return $this->createJsonResponse($data);
	}

	public function ajaxGetMacroActionsAction()
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		return $this->createJsonResponse(array(
			'macro_id' => $macro['id'],
			'macro_actions' => $macro['actions']
		));
	}

	public function ajaxPreviewActionsAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		// Use a dummy TicketMacro so we can get an actions array easily
		$macro = new Entity\TicketMacro();

		$action_rules = RuleBuilder::newActionsBuilder();
		$got_actions = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw', 'string'));

		if ($this->in->getString('message')) {
			$got_actions[] = array('type' => 'reply', array('options' => array('new_reply' => $this->in->getString('message'))));
		}

		$macro['actions'] = $got_actions;

		$action_collections = $macro->getActionsCollectionsForTickets($tickets);

		$actions = array();
		foreach ($action_collections as $ticket_id => $collection) {
			$actions[$ticket_id] = $collection->getApplyActions($tickets[$ticket_id], $this->person);
		}

		$data = array();
		$data['raw_actions'] = array();
		$data['ticket_actions'] = $actions;

		$raw_actions = $macro->getActionsCollection();

		if ($raw_actions->hasActionType('new_reply')) {
			$data['raw_actions']['new_reply'] = $raw_actions->getActionType('new_reply');
		}

		return $this->createJsonResponse($data);
	}

	public function ajaxSaveActionsAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('result_ids', 'uint', 'discard');
		$tickets = App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		// Accept changes to apply for previewing
		// - We just apply the changes but dont save them, they'll be
		//   properly displayed in the listing.
		$actions = $this->in->getCleanValueArray('actions', 'raw', 'string');

		$this->em->beginTransaction();

		if ($actions && $tickets) {
			$factory = new ActionsFactory();
			$collection = new ActionsCollection();

			foreach ($actions as $name => $opt) {
				$action = $factory->createFromForm($name, $opt);
				$collection->add($action);
			}

			foreach ($tickets as $t) {
				$collection->apply($t, $this->person);
				$this->em->persist($t);
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array('success' => true));
	}

	public function ajaxSaveMacroAction()
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		App::getOrm()->beginTransaction();

		$reply = $this->in->getString('message');

		foreach ($tickets as $ticket) {
			$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
			$actions = $macro->getActionsArray($ticket);
			if ($reply) {
				$actions['new_reply'] = $reply;
			}
			$result = $ticket_edit->applyActions($actions);
			$ticket_edit->save();

			// We need to manually apply to the user since ticketedit doesnt care about that
			$macro->performOnPerson($ticket['person']);
			App::getOrm()->persist($ticket['person']);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true));
	}

	public function ajaxMassReplyAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		App::getOrm()->beginTransaction();

		$reply = $this->in->getString('message');

		foreach ($tickets as $ticket) {
			$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
			$actions = array('new_reply' => $reply);
			$result = $ticket_edit->applyActions($actions);
			$ticket_edit->save();
		}

		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true));
	}
}
