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

use \Application\DeskPRO\Searcher\TicketSearch;
use \Application\DeskPRO\Entity\TicketQueue;
use \Application\DeskPRO\Entity\Ticket;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('AgentBundle:TicketSearch:list-blank.twig.html');
	}

	public function queuesPaneAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		$order = $this->person->getPref('agent.ui.ticket-queues-order');
		if ($order) {
			$queues_unordered = $queues;
			$queues = array();

			foreach ($order as $id) {
				if (isset($queues_unordered[$id])) {
					$queues[$id] = $queues_unordered[$id];
					unset($queues_unordered[$id]);
				}
			}

			if (count($queues_unordered)) {
				foreach ($queues_unordered as $id => $q) {
					$queues[$id] = $q;
				}
			}
		}

		return $this->render('AgentBundle:TicketSearch:pane-queues.twig.html', array(
			'queues' => $queues
		));
	}

	public function runQueueAction($queue_id)
	{
		$queue = App::getEntityRepository('DeskPRO:TicketQueue')->find($queue_id);
		$results_helper = Helper\TicketResults::newFromQueue($this, $queue);

		$vars = array(
			'queue' => $queue,
			'queue_id' => $queue['id'],
		);

		$pref_display_fields = $this->person->getPref('agent.ui.ticket-queue-display-fields.' . $queue['id']);
		if ($pref_display_fields) {
			$vars['display_fields'] = $pref_display_fields;
		}

		return $this->_getResponseForTickets('queue', $queue['id'], $results_helper, $vars);
	}

	protected function _getResponseForTickets($type, $type_id, $results_helper, array $vars = array())
	{
		$is_partial = false;
		$tpl = 'AgentBundle:TicketSearch:'.$type.'-results.twig.html';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:TicketSearch:part-results-list.twig.html';
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

		if (!$this->in->checkIsset('group_field_id')) {
			// User looking at all results
			$is_grouping = false;
			$tickets = $results_helper->getTicketsForPage($page);
		} else {
			// User looking at just a group of results
			$is_grouping = true;
			$tickets = $results_helper->getGroupedTicketsForPage($this->in->getString('group_field_id'), $page);
		}

		#------------------------------
		# Send results
		#------------------------------

		if (!count($tickets) && $is_partial) {
			return $this->createJsonResponse(array('no_more_results' => true));
		}

		$flagged_tickets = App::getEntityRepository('DeskPRO:TicketFlagged')->getFlagsForTickets($tickets, $this->person);

		if (empty($vars['display_fields'])) {
			$vars['display_fields'] = array('person', 'department');
		}

		$macros = null;
		if (!$is_partial) {
			$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);
		}

		$vars = array_merge($vars, array(
			'type'               => $type,
			'type_id'            => $type_id,
			'tickets'            => $tickets,
			'flagged_tickets'    => $flagged_tickets,
			'page'               => $page,
			'macros'             => $macros,
			'show_flag'          => true,
			'grouped_info'       => $grouped_info,
			'is_grouped_result'  => $is_grouping,
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

	############################################################################
	# find-pane
	############################################################################

	public function findPaneAction()
	{
		return $this->render('AgentBundle:TicketSearch:pane-find.twig.html', array(

		));
	}

	public function filterAction()
	{
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		// Used to specify terms in the URL and have then show up automatically
		$preselect_terms = $this->in->getCleanValueArray('terms', 'raw' , 'discard');
		$autorun = $this->in->getBool('autorun');

		return $this->render('AgentBundle:TicketSearch:filter-form.twig.html', array(
			'ticket_options' => $ticket_options,
			'preselect_terms' => $preselect_terms,
			'autorun' => $autorun
		));
	}

	public function searchAction()
	{
		// Used to specify terms in the URL and have then show up automatically
		$preselect_query = $this->in->getString('search_query');
		$autorun = $this->in->getBool('autorun');

		return $this->render('AgentBundle:TicketSearch:search.twig.html', array(
			'preselect_query' => $preselect_query,
			'autorun' => $autorun
		));
	}

	public function runFilterAction()
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
			$terms = $this->in->getCleanValueArray('terms', 'raw' , 'discard');

			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			foreach ($terms as $term) {
				$data = $term;
				unset($data['rule_type'], $data['op']);

				if (count($data) == 1) {
					$data = array_pop($data);
				}

				$searcher->addTerm($term['rule_type'], $term['op'], $data);
			}

			$order_by = $this->in->getString('filter.order_by');
			$group_by = $this->in->getString('filter.group_by');

			//TODO remove when ready for real searches, make it an option in UI
			$searcher->enableArchiveSearch();

			if ($order_by) {
				$searcher->setOrderByCode($order_by);
			}

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $searcher->getTerms(), 'order_by' => $order_by, 'group_by' => $group_by);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

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
			$searcher->enableArchiveSearch();

			$results = $searcher->getMatches();
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Serve results
		#------------------------------

		$results_helper = Helper\TicketResults::newFromResultCache($this, $result_cache);

		$vars = array(
			'cache' => $result_cache,
			'cache_id' => $result_cache['id']
		);

		if (!empty($result_cache['extra']['display_fields'])) {
			$vars['display_fields'] =$result_cache['extra']['display_fields'];
		}

		$pref_name = 'agent.ui.ticket-filter-display-fields.' . $result_cache['id'];
		if (!empty($result_cache['extra'][$pref_name])) {
			$vars['display_fields'] = $result_cache['extra'][$pref_name];
		}

		return $this->_getResponseForTickets('filter', $result_cache['id'], $results_helper, $vars);
	}

	public function _runFilterFromReq($terms = null)
	{
		$result_cache = false;
		if ($this->in->getUint('cache_id')) {
			$result_cache = App::getEntityRepository('DeskPRO:ResultCache')->find($this->in->getUint('cache_id'));
			if ($result_cache['person_id'] != $this->person['id']) {
				$result_cache = false;
			}
		}

		#------------------------------
		# If there's no result set, we're running
		# it for the first time
		#------------------------------

		if (!$result_cache) {

			if (!$terms) {
				$terms = $this->in->getCleanValueArray('terms', 'raw' , 'discard');
			}

			$searcher = new \Application\DeskPRO\Searcher\TicketSearch();
			foreach ($terms as $term) {
				$data = $term;
				unset($data['rule_type'], $data['op']);

				if (count($data) == 1) {
					$data = array_pop($data);
				}

				$searcher->addTerm($term['rule_type'], $term['op'], $data);
			}

			//TODO remove when ready for real searches, make it an option in UI
			$searcher->enableArchiveSearch();

			$results = $searcher->getMatches();

			$result_cache = new Entity\ResultCache();
			$result_cache['person'] = $this->person;
			$result_cache['criteria'] = array('terms' => $terms);
			$result_cache['results'] = $results;
			$result_cache['num_results'] = count($results);

			App::getOrm()->persist($result_cache);
			App::getOrm()->flush();
		}

		#------------------------------
		# Now fetch tickets
		#------------------------------

		$total = $result_cache['num_results'];
		$per_page = 50;
		$num_pages = ceil($total / $per_page);

		$cur_page = $this->in->getUint('page');
		if (!$cur_page OR $cur_page > $num_pages) $cur_page = 1;

		$start_at = ($cur_page - 1) * $per_page;

		$ticket_ids = array_slice($result_cache['results'], $start_at, $per_page);
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		$data = array(
			'cache_id' => $result_cache['id'],
			'total' => $total
		);

		$view_params = array(
			'tickets' => $tickets,
			'display_fields' => array('department', 'agent', 'person')
		);

		if ($cur_page == 1) {
			$data['html'] = $this->renderView('AgentBundle:TicketSearch:filter-results.twig.html', $view_params);
		} else {
			$data['is_partial'] = true;
			$data['html'] = $this->renderView('AgentBundle:TicketSearch:filter-results-page.twig.html', $view_params);
		}

		return $data;
	}
	
	############################################################################
	# overview-pane
	############################################################################

	public function overviewPaneAction()
	{
		return $this->render('AgentBundle:TicketSearch:pane-overview.twig.html', array(

		));
	}

	public function overviewNavAction()
	{
		$group1 = $this->in->getString('group1');
		$group2 = $this->in->getString('group2');

		$grouper = new \Application\DeskPRO\Tickets\GroupingCounter();
		$grouper->setGrouping($group1, $group2);
		$grouper->setMode($this->in->getString('mode'), $this->person['id']);

		$filter_agent_id = null;
		if ($this->in->getString('mode') == 'your') {
			$filter_agent_id = $this->person['id'];
		} elseif ($this->in->getString('mode') == 'unassigned') {
			$filter_agent_id = 0;
		} else {
			$filter_agent_id = -2;
		}

		$display_counts = $grouper->getDisplayArray();

		unset($display_counts[0]);// TODO 0 is the 'total', we'll use that later in the UI

		// TODO: Need a cleaner way of converting a group into a searchable item
		$group1_nosuf = preg_replace('#_id$#', '', $group1);
		$list_url_group1 = $this->generateUrl('agent_ticketsearch_runoverview') . "?autorun=true&terms[0][rule_type]=agent&terms[0][op]=is&terms[0][agent]=$filter_agent_id&terms[1][rule_type]=status&terms[1][op]=is&terms[1][status]=awaiting_agent&terms[2][rule_type]=$group1_nosuf&terms[2][op]=is&terms[2][$group1_nosuf]=\$group1_id";

		$group2_nosuf = preg_replace('#_id$#', '', $group2);
		$list_url_group2 = $list_url_group1 . "&terms[3][rule_type]=$group2_nosuf&terms[3][op]=is&terms[3][$group2_nosuf]=\$group2_id";

		return $this->render('AgentBundle:TicketSearch:overview-listing.twig.html', array(
			'counts' => $display_counts,
			'list_url_group1' => $list_url_group1,
			'list_url_group2' => $list_url_group2,
		));
	}

	public function overviewRunAction()
	{
		$data = $this->_runFilterFromReq();
		
		return $this->render('AgentBundle:TicketSearch:overview-results.twig.html', $data);
	}
	
	############################################################################
	# labels-pane
	############################################################################
	
	public function labelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('ticket', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();

		return $this->render('AgentBundle:TicketSearch:pane-labels.twig.html', array(
			'cloud' => $cloud
		));
	}

	public function runLabelAction($label)
	{
		$terms = array();
		$terms[] = array(
			'rule_type' => 'label',
			'op' => 'is',
			'label' => $label
		);
		$data = $this->_runFilterFromReq($terms);

		return $this->render('AgentBundle:TicketSearch:overview-results.twig.html', $data);
	}


	############################################################################
	# flagged
	############################################################################

	public function flaggedPaneAction()
	{
		return $this->render('AgentBundle:TicketSearch:pane-flagged.twig.html', array(

		));
	}

	public function runFlaggedAction($flag)
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromFlagged($flag, $this->person, $page, 50);

		$tpl = 'AgentBundle:TicketSearch:flagged-results.twig.html';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:part-results-list.twig.html';
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

		$op = $this->in->getString('op');
		if ($op == 'macro') {
			$macro = App::getOrm()->getRepository('DeskPRO:TicketMacro')->find($this->in->getUint('macro_id'));

			if (!$macro) {
				// TODO handle err
			}
		}

		App::getOrm()->beginTransaction();

		foreach ($tickets as $ticket) {
			switch ($op) {
				case 'macro':
					$macro->performOnTicket($ticket);
					break;

				case 'take':
					$ticket['agent'] = $this->person;
					App::getOrm()->persist($ticket);
					break;

				case 'delete':
					App::getOrm()->remove($ticket);
					break;

				case 'spam':
					$ticket['status'] = Ticket::STATUS_HIDDEN;
					$ticket['hidden_status'] = Ticket::HIDDEN_STATUS_SPAM;
					App::getOrm()->persist($ticket);
					break;

				case 'status':
					switch ($this->in->getString('status')) {
						case 'awaiting_agent':
							$ticket['status'] = Ticket::STATUS_OPEN;
							break;

						case 'pending':
							$ticket['status'] = Ticket::STATUS_PENDING;
							break;

						case 'resolved':
							$ticket['status'] = Ticket::STATUS_RESOLVED;
							break;
					}

					App::getOrm()->persist($ticket);
					break;
			}
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

		return $this->createJsonResponse($actions);
	}

	public function ajaxSaveMacroAction()
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		App::getOrm()->beginTransaction();

		foreach ($tickets as $ticket) {
			$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
			$actions = $macro->getActionsArray($ticket);
			$result = $ticket_edit->applyActions($actions);
			$ticket_edit->save();
		}

		App::getOrm()->commit();

		return $this->createJsonResponse(array('success' => true));
	}
}