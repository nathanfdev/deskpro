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
				$queues[$id] = $queues_unordered[$id];
				unset($queues_unordered[$id]);
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
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$tickets = App::getApi('tickets.queues')->getTicketsFromQueue($queue_id, $page, 50);
		$flagged_tickets = App::getEntityRepository('DeskPRO:TicketFlagged')->getFlagsForTickets($tickets, $this->person);

		$tpl = 'AgentBundle:TicketSearch:queue-results.twig.html';
		if ($this->in->getBool('partial')) {
			$tpl = 'AgentBundle:TicketSearch:part-results-list.twig.html';
			if (!count($tickets)) {
				return $this->createResponse('');
			}
		}

		$display_fields = $this->person->getPref('agent.ui.ticket-queues-display-fields.' . $queue_id);

		if (!$display_fields) {
			$display_fields = array('person', 'department');
		}

		$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		return $this->render($tpl, array(
			'queue_id' => $queue_id,
			'tickets' => $tickets,
			'flagged_tickets' => $flagged_tickets,
			'page' => $page,
			'display_fields' => $display_fields,
			'macros' => $macros,
			'ticket_flagged_color' => 'none',
			'show_flag' => true
		));
	}

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
		
		return $this->render('AgentBundle:TicketSearch:filter.twig.html', array(
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

	public function _runFilterFromReq()
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

	public function runFilterAction()
	{
		$data = $this->_runFilterFromReq();

		return $this->createJsonResponse($data);
	}
	
	############################################################################
	# labels-pane
	############################################################################
	
	public function labelsPaneAction()
	{
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('ticket', 25);
		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$cloud = $cloud_gen->getCloud();
		print_r($cloud);

		return $this->render('AgentBundle:TicketSearch:pane-labels.twig.html', array(
			'cloud' => $cloud
		));
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
							$ticket['status'] = Ticket::STATUS_AWAITING_AGENT;
							break;

						case 'awaiting_user':
							$ticket['status'] = Ticket::STATUS_AWAITING_USER;
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
}