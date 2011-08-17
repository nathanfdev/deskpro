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

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Dates;

use Application\DeskPRO\Search\Adapter\AbstractAdapter as AbstractSearchAdapter;
use Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection;
use Application\DeskPRO\EventDispatcher\PropertyChangedCallback;

use Application\DeskPRO\Tickets\TicketSplit;
use Application\DeskPRO\Tickets\TicketMerge\TicketMerge;

/**
 * Handles ticket searches
 */
class TicketController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, true);
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$ticket_attachments = App::getEntityRepository('DeskPRO:TicketAttachment')->getTicketAttachments($ticket);

		$counts = $this->_fetchTicketCounts($ticket);

		#------------------------------
		# Custom fields
		#------------------------------

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		#------------------------------
		# Messages
		#------------------------------

		if (($ticket_messages_blockcache = App::getEntityRepository('DeskPRO:Cache')->load("ticket_messages.{$ticket['id']}.agent_block")) === false) {

			$ticket_messages_blockcache = $this->_getMessageBlockInfo($ticket, 0, 0, $ticket_attachments);

			App::getEntityRepository('DeskPRO:Cache')->save("ticket_messages.{$ticket['id']}.agent_block", $ticket_messages_blockcache, 259200);
		}

		$ticket_messages_block = $ticket_messages_blockcache['ticket_messages_block'];
		$counts['messages'] = $ticket_messages_blockcache['message_count'];

		$ticket_flagged = APp::getOrm()->getRepository('DeskPRO:TicketFlagged')->find(array(
			'ticket_id' => $ticket_id,
			'person_id' => $this->person['id']
		));

		$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		$tpl = 'AgentBundle:Ticket:view.html.twig';
		if ($this->in->getBool('print')) {
			$tpl = 'AgentBundle:Ticket:view-print.html.twig';
		}

		// Get or update the lock on this ticket
		if (!$ticket->isLocked()) {
			$ticket->setLockedByAgent($this->person);

			$lock_cm = new ClientMessage();
			$lock_cm->fromArray(array(
				'channel' => 'agent-notification.tickets.unlocked',
				'data' => array(
					'ticket_id' => $ticket['id'],
					'agent_id' => $ticket['id'],
				),
				'created_by_client' => $this->session->getEntity()->getId(),
			));

			App::getOrm()->persist($lock_cm);
			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
		}

		// Widgets
		$widget_recs = App::getEntityRepository('DeskPRO:Widget')->getWidgetsForSection('agent.ticket');
		$widgets = array();
		if (count($widget_recs)) {
			$widgets = \Application\DeskPRO\Widgets\Factory::createHandlersForWidgets(
				$widget_recs,
				array('ticket' => $ticket, 'person' => $this->person)
			);
		}

		$widgets = Arrays::groupItems($widgets, 'section', true);
		if (!isset($widgets['agent.ticket.tabs'])) $widgets['agent.ticket.tabs'] = array();
		if (!isset($widgets['agent.ticket.display'])) $widgets['agent.ticket.display'] = array();

		$ticket_deleted = null;
		$hard_delete_time = null;
		if ($ticket['hidden_status'] == 'deleted') {
			$ticket_deleted = $ticket->getDeletionRecord();

			$date_deleted = $ticket['date_created'];
			if ($ticket_deleted['date_created']) {
				$date_deleted = $ticket_deleted['date_created'];
			}

			$hard_delete_time = $date_deleted->getTimestamp() + App::getSetting('core_tickets.hard_delete_time');
			$hard_delete_time = max(0, $hard_delete_time - time());

			if ($hard_delete_time) {
				$hard_delete_time = Dates::secsToReadable($hard_delete_time);
			}
		}

		// Check if the search adapter
		$show_related_content = false;
		//if (App::getSearchEngine()->isCapable(AbstractSearchAdapter::CAP_TICKETS_SIMILAR)
		//	OR App::getSearchEngine()->isCapable(AbstractSearchAdapter::CAP_CONTENT_TICKET_SIMILAR_ARTICLES)
		//) {
		//	$show_related_content = true;
		//})

		$participants = APp::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		$draft_pref = App::getOrm()->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => "ticket_draft.{$ticket['id']}"));
		$draft_text = '';
		if ($draft_pref) {
			$draft_text = $draft_pref->getValue();
		}

		return $this->render($tpl, array(
			'ticket' => $ticket,
			'ticket_attachments' => $ticket_attachments,

			'draft_text' => $draft_text,

			'last_message_id' => $ticket_messages_blockcache['last_message_id'],
			'last_log_id' => $ticket_messages_blockcache['last_log_id'],

			'participants' => $participants,

			'custom_fields' => $custom_fields,

			'show_related_content' => $show_related_content,

			'ticket_messages_block' => $ticket_messages_block,

			'ticket_deleted' => $ticket_deleted,
			'hard_delete_time' => $hard_delete_time,
			'ticket_options' => $ticket_options,
			'ticket_flagged_color' => $ticket_flagged ? $ticket_flagged['color'] : 'none',
			'macros' => $macros,
			'widgets' => $widgets,
			'counts' => $counts,

			'agent_signature' => $this->person->getPref('agent.ticket_signature')
		));
	}

	protected function _getMessageBlockInfo($ticket, $since_message_id = 0, $since_log_id = 0, array $ticket_attachments = null)
	{
		$message_count = 0;
		$note_count = 0;

		$ticket_messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages(
			$ticket,
			array('since_id' => $since_message_id)
		);

		if (!$ticket_attachments) {
			$ticket_attachments = App::getEntityRepository('DeskPRO:TicketAttachment')->getAttachmentsForMessages($ticket_messages);
		}
		
		// Group attachments into messages so we can place them into each message
		$ticket_message_attachments = array();
		foreach ($ticket_attachments as $attach) {
			if (!isset($ticket_message_attachments[$attach['message']['id']])) {
				$ticket_message_attachments[$attach['message']['id']] = array();
			}

			$ticket_message_attachments[$attach['message']['id']][] = $attach['id'];
		}
		
		$ticket_logs = App::getEntityRepository('DeskPRO:TicketLog')->getLogsForTicket(
			$ticket,
			array('since_id' => $since_log_id)
		);
		$ticket_message_logs = array();

		$last_message_id = 0;
		$last_log_id = 0;
		
		foreach ($ticket_messages as $m) {
			if ($m['id'] > $last_message_id) {
				$last_message_id = $m['id'];
			}

			if ($m['is_agent_note']) {
				$note_count++;
			} else {
				$message_count++;
			}
		}
		foreach ($ticket_logs as $l) {
			if ($l['id'] > $last_log_id) {
				$last_log_id = $l['id'];
			}
		}

		// Sort log items into messages
		// - $ticket_message_logs[123] is an array of log items that should be displayed before it
		// - $ticket_message_logs[123] is an array of remaining log items (ie after last message)
		$log_keys = array_keys($ticket_logs);

		$before_m = null;
		foreach ($ticket_messages as $m) {
			if (!$before_m) {
				$before_m = $m;
				continue;
			}

			foreach ($log_keys as $thisk => $k) {
				$l = $ticket_logs[$k];

				if ($l['date_created'] >= $before_m['date_created'] && $l['date_created'] < $m['date_created']) {
					$ticket_message_logs[$before_m['id']][] = $l['id'];
					unset($log_keys[$thisk]);
				}
			}

			$before_m = $m;
		}

		if ($log_keys) {
			$ticket_message_logs['after'] = array();
			foreach ($log_keys as $k) {
				$l = $ticket_logs[$k];
				$ticket_message_logs['after'][] = $l['id'];
				unset($log_keys[$k]);
			}
		}
		
		$ticket_messages_block = '';

		if ($ticket_messages) {
			$ticket_messages_block = $this->renderView('AgentBundle:Ticket:ticket-messages-batch.html.twig', array(
				'ticket' => $ticket,
				'ticket_messages' => $ticket_messages,
				'ticket_message_attachments' => $ticket_message_attachments,
				'ticket_attachments' => $ticket_attachments,
				'ticket_message_logs' => $ticket_message_logs,
				'ticket_logs' => $ticket_logs,
			));
		}

		$ticket_messages_blockcache = array(
			'ticket_messages_block' => $ticket_messages_block,
			'message_count' => $message_count,
			'note_count' => $note_count,
			'last_message_id' => $last_message_id,
			'last_log_id' => $last_log_id,
		);

		return $ticket_messages_blockcache;
	}

	protected function _fetchTicketCounts($ticket)
	{
		$counts = array();

		$counts['attachments'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_attachments
			WHERE ticket_id = ?
		", array($ticket['id']));

		$counts['logs'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_logs
			WHERE ticket_id = ?
		", array($ticket['id']));

		$counts['messages'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_messages
			WHERE ticket_id = ?
		", array($ticket['id']));

		$counts['notes'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_messages
			WHERE ticket_id = ? AND is_agent_note = 1
		", array($ticket['id']));

		return $counts;
	}

	public function getUpdatedCountsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		return $this->createJsonResponse($this->_fetchTicketCounts($ticket));
	}

	############################################################################
	# view-tip
	############################################################################

	/**
	 * Serves up a tool-tip description for the ticket
	 *
	 * @param  $ticket_id
	 */
	public function viewTipAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$message = null;
		try {
			$message = App::getEntityRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($ticket);
		} catch (\Exception $e) {};

		return $this->render('AgentBundle:Ticket:ticket-tip.html.twig', array(
			'ticket' => $ticket,
			'message' => $message
		));
	}

	############################################################################
	# snippets-viewer
	############################################################################

	public function snippetsViewerAction($ticket_id = 0)
	{
		if ($ticket_id) {
			$ticket = $this->getTicketOr404($ticket_id);
			$person = $ticket->person;
		} else {
			$ticket = null;
			$person = null;
		}

		$ticket_snippets = App::getEntityRepository('DeskPRO:TicketSnippet')->getSnippetsForAgent($this->person);
		$ticket_snippet_cats = App::getEntityRepository('DeskPRO:TicketSnippetCategory')->getCatsForAgent($this->person);

		$agent_teams = App::getEntityRepository('DeskPRO:AgentTeam')->findAll();

		return $this->render('AgentBundle:Ticket:ticket-snippets.html.twig', array(
			'ticket' => $ticket,
			'person' => $person,
			'ticket_snippets' => $ticket_snippets,
			'ticket_snippet_cats' => $ticket_snippet_cats,
			'agent_teams' => $agent_teams,
		));
	}

	public function newSnippetCatAction()
	{
		$cat = new \Application\DeskPRO\Entity\TicketSnippetCategory();
		$cat['title'] = $this->in->getString('title');
		$cat->person = $this->person;

		if ($this->in->getString('perm_type') == 'global') {
			$cat['is_global'] = true;
		} elseif ($this->in->getString('perm_type') == 'team') {
			$team_ids = $this->in->getArrayValue('teams');
			$teams = $this->em->getRepository('DeskPRO:AgentTeam')->getTeamsFromIds($team_ids);

			foreach ($teams as $t) {
				$cat->agent_teams->add($t);
			}
		}

		$this->em->transactional(function($em) use ($cat) {
			$em->persist($cat);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'cat_row_html' => $this->renderView('AgentBundle:Ticket:ticket-snippets-catrow.html.twig', array(
				'category' => $cat
			)),
			'cat_section_html' => $this->renderView('AgentBundle:Ticket:ticket-snippets-catsection.html.twig', array(
				'category' => $cat,
				'snippets' => array()
			))
		));
	}

	public function editSnippetCatAction()
	{
		$cat = App::findEntity('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));

		return $this->render('AgentBundle:Ticket:ticket-snippets-editcat.html.twig', array(
			'category' => $cat,
		));
	}

	public function saveSnippetCatAction()
	{
		$cat = App::findEntity('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));
		$cat['title'] = $this->in->getString('title');

		return $this->createJsonResponse(array(
			'category_id' => $cat['id'],
			'title' => $cat['title']
		 ));
	}

	public function deleteSnippetCatAction()
	{
		$cat = App::findEntity('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));

		$cat_id = $cat['id'];

		$this->em->transactional(function($em) use ($cat) {
			$em->remove($cat);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'category_id' => $cat_id,
		 ));
	}

	public function saveSnippetAction()
	{
		if ($this->in->getUint('snippet_id')) {
			$snippet = App::findEntity('DeskPRO:TicketSnippet', $this->in->getUint('snippet_id'));
			$category = $snippet->category;
		} else {
			$category = App::findEntity('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));
			$snippet = new \Application\DeskPRO\Entity\TicketSnippet();
			$snippet->category = $category;
		}

		$snippet['title'] = $this->in->getString('title');
		$snippet['snippet'] = $this->in->getString('snippet');
		$snippet->person = $this->person;

		$this->em->transactional(function($em) use ($snippet) {
			$em->persist($snippet);
			$em->flush();
		});

		if ($this->in->getUint('ticket_id')) {
			$ticket = $this->getTicketOr404($this->in->getUint('ticket_id'));
			$person = $ticket->person;
		} else {
			$ticket = null;
			$person = null;
		}

		return $this->createJsonResponse(array(
			'snippet_row_html' => $this->renderView('AgentBundle:Ticket:ticket-snippets-row.html.twig', array(
				'snippet' => $snippet,
				'ticket' => $ticket,
				'person' => $person,
			)),
			'snippet_id' => $snippet['id'],
			'category_id' => $category['id']
		));
	}

	public function deleteSnippetAction()
	{
		$snippet = App::findEntity('DeskPRO:TicketSnippet', $this->in->getUint('snippet_id'));

		$snippet_id = $snippet['id'];
		$category_id = $snippet->category['id'];

		$this->em->transactional(function($em) use ($snippet) {
			$em->remove($snippet);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'snippet_id' => $snippet_id,
			'category_id' => $category_id
		));
	}

	############################################################################
	# Ajax loaded tabs
	############################################################################

	public function ajaxTabTicketLogAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_logs = App::getOrm()->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket);

		return $this->render('AgentBundle:Ticket:tab-ticketlog.html.twig', array(
			'ticket_logs' => $ticket_logs
		));
	}

	public function ajaxTabAttachmentsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		return $this->render('AgentBundle:Ticket:tab-attachments.html.twig', array(
			'ticket' => $ticket
		));
	}

	public function ajaxTabRelatedContentAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$search = App::getSearchEngine();


		$related_tickets = false;
		if (App::getSearchEngine()->isCapable(AbstractSearchAdapter::CAP_TICKETS_SIMILAR)) {
			$ticket_searcher = $search->getTicketSearcher();
			$results = $ticket_searcher->similar($ticket);

			$related_tickets = $search->getResultSetObjects($results);
		}

		$related_articles = false;
		if (App::getSearchEngine()->isCapable(AbstractSearchAdapter::CAP_CONTENT_TICKET_SIMILAR_ARTICLES)) {
			$content_searcher = $search->getContentSearcher();
			$results = $content_searcher->similarArticleToTicket($ticket);

			$related_articles = $search->getResultSetObjects($results);
		}

		return $this->render('AgentBundle:Ticket:tab-related-content.html.twig', array(
			'ticket'            => $ticket,
			'related_tickets'   => $related_tickets,
			'related_articles'  => $related_articles,
		));
	}

	############################################################################
	# ajax-save-flagged
	############################################################################

	public function ajaxSaveFlaggedAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket->setFlagForPerson($this->person, $this->in->getString('color'));

		return $this->createJsonResponse(array('success' => 1));
	}



	############################################################################
	# ajax-save-custom-fields
	############################################################################

	public function ajaxSaveCustomFieldsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		
		if (!empty($_POST['custom_fields'])) {
			foreach ($ticket_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
					$ticket->setCustomData($info[0], $info[1], $info[2]);
				}
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
		}

		// Custom fields
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		return $this->render('AgentBundle:Ticket:view-page-display-holders.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
		));
	}



	############################################################################
	# ajax-save-options
	############################################################################

	public function ajaxSaveOptionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if ($this->in->checkIsset('department')) {
			$ticket['department_id'] = $this->in->getUint('department');
		}
		if ($this->in->checkIsset('category')) {
			$ticket['category_id'] = $this->in->getUint('category');
		}
		if ($this->in->checkIsset('product')) {
			$ticket['product_id'] = $this->in->getUint('product');
		}
		if ($this->in->checkIsset('priority')) {
			$ticket['priority_id'] = $this->in->getUint('priority');
		}
		if ($this->in->checkIsset('status')) {
			$ticket['status'] = $this->in->getString('status');
		}
		if ($this->in->checkIsset('agent')) {
			$ticket['agent_id'] = $this->in->getUint('agent');
		}
		if ($this->in->checkIsset('agent_team')) {
			$ticket['agent_team_id'] = $this->in->getUint('agent_team');
		}

		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		$ticket_edit->save();

		return $this->createJsonResponse(array('success' => 1));
	}

	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$ticket->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# ajax-save-reply
	############################################################################

	public function ajaxSaveReplyAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		#------------------------------
		# Handle new message
		#------------------------------

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['ip_address'] = $this->request->getClientIp();
		$message->setMessageText($this->in->getString('message'));

		foreach ($this->in->getCleanValueArray('attach') as $blob_id) {

			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$message->addAttachment($attach);
		}

		if ($dupe_message = App::getEntityRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $ticket)) {
			return $this->createJsonResponse(array(
				'dupe_message' => true,
				'message_id' => $dupe_message['id']
			));
		} else {
			$ticket->addMessage($message);
		}

		#------------------------------
		# Handle actions
		#------------------------------

		if ($this->in->getBool('options.do_assign')) {
			$ticket['agent_id'] = $this->in->getUint('options.agent_id');
		}

		if ($this->in->getBool('options.do_assign_team')) {
			$ticket['agent_team_id'] = $this->in->getUint('options.agent_team_id');
		}

		if ($this->in->getBool('options.do_status')) {
			$ticket['status'] = $this->in->getString('options.status');
		}

		$kb_pending = false;
		if ($this->in->getBool('options.do_kbpending')) {
			$kb_pending = new ArticlePendingCreate();
			$kb_pending->fromArray(array(
				'person' => $this->person,
				'ticket' => $ticket
			));
		}

		if ($this->in->getBool('options.is_note')) {
			$message['is_agent_note'] = true;
		}

		#------------------------------
		# Handle CC'ing/parts
		#------------------------------

		$this->em->beginTransaction();

		$cc_person_ids = $this->in->getCleanValueArray('cc_person_ids', 'uint', 'discard');
		$new_parts = $this->in->getCleanValueArray('new_parts', 'string', 'discard');

		$new_parts_to_people = array();

		foreach ($new_parts as $email) {
			$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email);
			if (!$person) {
				$person = Person::newContactPerson(array('email' => $email));
				$this->em->persist($person);
			}

			if ($person['is_agent']) {
				continue;
			}

			$new_parts_to_people[] = $person;
		}
		$this->em->flush();

		foreach ($new_parts_to_people as $person) {
			$ticket->addParticipantPerson($person);
			$cc_person_ids[] = $person['id'];
		}

		$tracker = $ticket->getTicketLogger();
		$tracker->recordExtra('enabled_cc', $cc_person_ids);

		if ($kb_pending) {
			$this->em->persist($kb_pending);
		}

		// Delete any possible ticket draft
		$draft_pref = App::getOrm()->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => "ticket_draft.{$ticket['id']}"));
		if ($draft_pref) {
			App::getOrm()->remove($draft_pref);
		}
		
		$add_agent_parts = $this->in->getCleanValueArray('add_agent_part', 'uint', 'discard');
		foreach ($add_agent_parts as $aid) {
			$ticket->addParticipantPerson($aid);
		}

		$updated_agent_parts = false;
		$updated_agent_parts_count = 0;
		if ($add_agent_parts) {

			$added = false;
			foreach ($add_agent_parts as $p) {
				if (!$ticket->hasParticipantPerson($p)) {
					$ticket->addParticipantPerson($p);
					$added = true;
				}
			}

			if ($added) {
				$participants = App::getOrm()->createQuery("
					SELECT p
					FROM DeskPRO:TicketParticipant p
					LEFT JOIN p.person person
					LEFT JOIN p.person_email person_email
					WHERE p.ticket = ?1 AND person.is_agent = true
				")->setParameter(1, $ticket)->execute();

				$updated_agent_parts_count = count($participants);

				$updated_agent_parts = $this->renderView('AgentBundle:Ticket:view-participants-agents.html.twig', array(
					'ticket' => $ticket,
					'participants' => $participants
				));
			}
		}

		$this->em->persist($ticket);
		$this->em->flush();
		$this->em->commit();

		$client_messages = false;
		if ($this->in->getUint('client_messages_since')) {
			$client_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		$close_tab = $this->in->getBool('options.close_tab');

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$this->in->getUint('last_message_id'),
			$this->in->getUint('last_log_id')
		);

		$data = array_merge($data, array(
			'updated_agent_parts_html' => $updated_agent_parts,
			'updated_agent_parts_html_count' => $updated_agent_parts_count,
			'agent_id' => $ticket['agent_id'],
			'agent_team_id' => $ticket['agent_team_id'],
			'status' => $ticket['status'],
			'close_tab' => $close_tab,
			'client_messages' => $client_messages,
		));

		return $this->createJsonResponse($data);
	}

	public function ajaxUpdateCheckAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$this->in->getUint('last_message_id'),
			$this->in->getUint('last_log_id')
		);

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-save-actions
	############################################################################

	public function ajaxSaveActionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		$result = $ticket_edit->applyActions($this->in->getCleanValueArray('actions', 'raw', 'raw'));

		// If department is changed,
		// then we re-output the holder template
		$is_dep_changed = false;
		$event_listener = new PropertyChangedCallback(function ($sender, $propertyName, $oldValue, $newValue) use (&$is_dep_changed) {
			if ($propertyName == 'department') {
				$is_dep_changed = true;
			}
		});
		$ticket->addPropertyChangedListener($event_listener);

		App::getOrm()->beginTransaction();

		$macro_id = $this->in->getUint('macro_id');
		if ($macro_id) {
			$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);
			$all_macro_actions = $macro->getActionsArray($ticket);
			$apply_macro_actions = array();

			// Only ticket fields need to be applied this way,
			// the other actions were performed on the actual ticket interface
			// and sent in the request, and applied normally above
			foreach ($all_macro_actions as $k => $action) {
				if (strpos($k, 'ticket_field') === 0) {
					$apply_macro_actions[$k] = $action;
				}
			}

			// We need to manually apply to the user since ticketedit doesnt care about that
			$macro->performOnPerson($ticket['person']);

			if ($apply_macro_actions) {
				$ticket_edit->applyActions($apply_macro_actions);
			}
		}

		if (!empty($_POST['custom_fields'])) {
			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			
			foreach ($ticket_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
					$ticket->setCustomData($info[0], $info[1], $info[2]);
				}
			}
		}

		$ticket_edit->save();

		App::getOrm()->flush();
		App::getOrm()->commit();

		$data = array('data' => array());
		if (isset($result['new_reply'])) {
			$data['data']['new_reply'] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $result['new_reply']
			));
		}

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$data['holders'] = $this->renderView('AgentBundle:Ticket:view-page-display-holders.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields
		));

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-get-macro-actions
	############################################################################

	public function ajaxGetMacroAction($ticket_id)
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		$ticket = null;
		if ($ticket_id) {
			$ticket = $this->getTicketOr404($ticket_id);
		}

		$actions_collection = $macro->getActionsCollection($ticket);
		$actions = $actions_collection->getApplyActions($ticket, $this->person);

		return $this->createJsonResponse($actions);
	}


	############################################################################
	# view-message-details
	############################################################################

	public function viewMessageDetailsAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);
		$ticket = $message->ticket;

		return $this->render('AgentBundle:Ticket:message-details.html.twig', array(
			'message' => $message,
			'ticket' => $ticket
		));
	}

	public function viewUnformattedMessageAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);

		return $this->render('AgentBundle:Ticket:message-details-unformatted.html.twig', array(
			'message' => $message,
			'ticket' => $message['ticket']
		));
	}

	public function viewEmailSourceAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);

		return $this->render('AgentBundle:Ticket:message-details-email-source.html.twig', array(
			'message' => $message,
			'ticket' => $message['ticket']
		));
	}

	public function ajaxGetMessageQuoteAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);

		$message_quote = wordwrap($message->getMessageText(), 75, "\n", true);
		$message_quote = preg_replace('#^#m', "> ", $message_quote);

		return $this->createJsonResponse(array(
			'message_id' => $message['id'],
			'message_quote' => $message_quote
		));
	}

	############################################################################
	# get-ticket-messages
	############################################################################

	public function ajaxGetMessagesAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$data = array('messages' => array());

		$since = $this->in->getUint('since');

		$messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			WHERE m.ticket = ?1 AND m.id > ?2
		")->execute(array(1=>$ticket, 2=> $since));

		foreach ($messages as $message) {
			$data['messages'][] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $message
			));

			if ($message['is_agent_note']) {
				$data['has_notes'] = true;
			}
		}

		return $this->createJsonResponse($data);
	}

	############################################################################
	# save-agent-parts, save-user-parts
	############################################################################

	public function saveAgentPartsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$set_agent_ids = $this->in->getCleanValueArray('person_ids', 'uint', 'discard');
		$ticket->setParticipantAgentIds($set_agent_ids);

		App::getOrm()->transactional(function($em) use ($ticket) {
			$em->persist($ticket);
			$em->flush();
		});

		$participants = APp::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		return $this->render('AgentBundle:Ticket:view-participants-agents.html.twig', array(
			'ticket' => $ticket,
			'participants' => $participants
		));
	}

	public function saveUserPartsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$set_user_ids = $this->in->getCleanValueArray('person_ids', 'uint', 'discard');
		$ticket->setParticipantUserIds($set_user_ids);

		App::getOrm()->transactional(function($em) use ($ticket) {
			$em->persist($ticket);
			$em->flush();
		});

		$participants = APp::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		return $this->render('AgentBundle:Ticket:view-participants-users.html.twig', array(
			'ticket' => $ticket,
			'participants' => $participants
		));
	}

	public function ccReplyTabAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		
		$participants = APp::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		return $this->render('AgentBundle:Ticket:view-reply-cctab.html.twig', array(
			'ticket' => $ticket,
			'participants' => $participants
		));
	}

	############################################################################
	# delete
	############################################################################

	/**
	 * Soft-deletes a ticket
	 *
	 * @param  $ticket_id
	 */
	public function deleteAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$reason = $this->in->getString('reason');

		App::getOrm()->beginTransaction();
		$ticket->deleteTicket($this->person, $reason);
		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}


	############################################################################
	# merge
	############################################################################

	public function mergeOverlayAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$tickets_by_user = $this->em->getRepository('DeskPRO:Ticket')->getLatestByUser($ticket->person);
		$open_tickets    = $this->em->getRepository('DeskPRO:Ticket')->getTicketsFromIds($this->in->getCleanValueArray('open_ticket_ids', 'uint', 'discard'));

		$fn = function ($t) use ($ticket) {
			if ($t['id'] == $ticket['id']) {
				return false;
			}
			return true;
		};

		$tickets_by_user = array_filter($tickets_by_user, $fn);
		$open_tickets    = array_filter($open_tickets, $fn);

		return $this->render('AgentBundle:Ticket:merge-overlay.html.twig', array(
			'ticket'          => $ticket,
			'tickets_by_user' => $tickets_by_user,
			'open_tickets'    => $open_tickets,
		));
	}

	/**
	 * Merge a ticket interface
	 */
	public function mergeAction($ticket_id, $other_ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$other_ticket = $this->getTicketOr404($other_ticket_id);

		$old_ticket_id = $other_ticket['id'];

		try {
			$this->em->beginTransaction();
			$merge = new TicketMerge($this->person, $ticket, $other_ticket);
			$merge->merge();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();

			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'ticket_id' => $ticket['id'],
			'old_ticket_id' => $old_ticket_id
		));
	}

	############################################################################
	# split
	############################################################################

	public function splitAction($message_id)
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

		$split = new TicketSplit($message);

		try {
			$this->em->beginTransaction();
			$new_ticket = $split->split();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();

			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'ticket_id' => $new_ticket['id']
		));
	}

	############################################################################
	# new
	############################################################################

	public function newAction()
	{
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		return $this->render('AgentBundle:Ticket:newticket.html.twig', array(
			'ticket_options' => $ticket_options,
		));
	}

	public function newSaveAction()
	{
		$newticket = new \Application\AgentBundle\Form\Model\NewTicket($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewTicket();
		$form = $this->get('form.factory')->create($formType, $newticket);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			$newticket->save();

			$ticket = $newticket->getTicket();

			return $this->createJsonResponse(array(
				'success' => true,
				'ticket_id' => $ticket['id']
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}

	public function newticketGetPersonRowAction($person_id)
	{
		$person = false;
		if ($person_id) {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		} elseif ($email = $this->in->getString('email_address')) {
			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
		}

		if (!$person) {
			$person = new Person();
		}

		return $this->render('AgentBundle:Ticket:newticket-person-row.html.twig', array(
			'person' => $person
		));
	}

	############################################################################

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id, $full = false)
	{
		$ticket = $this->em->find('DeskPRO:Ticket', $ticket_id);

		if (!$ticket) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}