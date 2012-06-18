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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

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
        $is_pdf = $this->in->getBool('pdf');
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$ticket_attachments = $this->em->getRepository('DeskPRO:TicketAttachment')->getTicketAttachments($ticket);
		if (!$ticket_attachments) $ticket_attachments = array();

		#------------------------------
		# Custom fields
		#------------------------------

		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		#------------------------------
		# Messages
		#------------------------------

		$ticket_messages_blockcache = $this->_getMessageBlockInfo($ticket, 0, 0, $ticket_attachments, $is_pdf);
		$ticket_messages_block = $ticket_messages_blockcache['ticket_messages_block'];
		$ticket_attachments = $ticket_messages_blockcache['ticket_attachments'];
		$ticket_message_attachments = isset($ticket_messages_blockcache['ticket_message_attachments']) ? $ticket_messages_blockcache['ticket_message_attachments'] : array();
		$counts['messages'] = $ticket_messages_blockcache['message_count'];

		$ticket_flagged = $this->em->getRepository('DeskPRO:TicketFlagged')->getFlagForTicket($ticket, $this->person);

		$macros = $this->em->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		$tpl = 'AgentBundle:Ticket:view.html.twig';

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

			$this->em->persist($lock_cm);
			$this->em->persist($ticket);
			$this->em->flush();
		}

		$hard_delete_time = null;
		$ticket_deleted = false;
		if ($ticket['hidden_status'] == 'deleted') {
			$ticket_deleted = $ticket->getDeletionRecord();

			$date_deleted = $ticket['date_created'];
			if ($ticket_deleted['date_created']) {
				$date_deleted = $ticket_deleted['date_created'];
			}

			$hard_delete_time = $date_deleted->getTimestamp() + $this->container->getSetting('core_tickets.hard_delete_time');
			$hard_delete_time = max(0, $hard_delete_time - time());

			if ($hard_delete_time) {
				$hard_delete_time = Dates::secsToReadable($hard_delete_time);
			}
		} elseif ($ticket['hidden_status'] == 'spam') {
			$hard_delete_time = $ticket->date_status->getTimestamp() + $this->container->getSetting('core_tickets.spam_delete_time');
			$hard_delete_time = max(0, $hard_delete_time - time());

			if ($hard_delete_time) {
				$hard_delete_time = Dates::secsToReadable($hard_delete_time);
			}
		}

		// Check if the search adapter
		$show_related_content = false;

		$participants = $ticket->participants;

		$participant_ids = array();
		$agent_parts = array();
		$user_parts = array();

		foreach ($participants as $p) {
			$participant_ids[] = $p->person->id;
			if ($p->person->is_agent) {
				$agent_parts[] = $p;
			} else {
				$user_parts[] = $p;
			}
		}

		$draft_pref = $this->em->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => "ticket_draft.{$ticket['id']}"));
		$draft_text = '';
		if ($draft_pref) {
			$draft_text = $draft_pref->getValue();
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

        $vars = array(
            'agents' => $agents,
            'agent_teams' => $agent_teams,

            'ticket_perms' => $this->_getTicketPerms($ticket),
            'ticket' => $ticket,
            'ticket_attachments' => $ticket_attachments,
			'ticket_message_attachments' => $ticket_message_attachments,

            'draft_text' => $draft_text,

            'last_message_id' => $ticket_messages_blockcache['last_message_id'],
            'last_log_id' => $ticket_messages_blockcache['last_log_id'],

            'participants' => $participants,
            'participant_ids' => $participant_ids,
            'agent_parts' => $agent_parts,
            'user_parts' => $user_parts,

            'custom_fields' => $custom_fields,

            'show_related_content' => $show_related_content,

            'ticket_messages_block' => $ticket_messages_block,

            'ticket_deleted' => $ticket_deleted,
            'hard_delete_time' => $hard_delete_time,
            'ticket_options' => $ticket_options,
            'ticket_flagged' => $ticket_flagged,
            'macros' => $macros,

            'agent_signature' => $this->person->getPref('agent.ticket_signature')
        );

        if($is_pdf)
        {
            $content_html = $this->renderView('DeskPRO:pdf_agent:view_ticket.html.twig', $vars);

            $mpdf = new \mPDF_mPDF
            (
                'utf-8', // Language/Character set
                'A4', // Size
                '8', // Default Font Size
                '', // Default Font
                20, // Margin Left
                20, // Margin Right
                40, // Margin Top
                40, // Margin Bottom
                10, // Margin Header
                10, // Margin Footer
                'P' // Orientation
            );

            $mpdf->SetBasePath(realpath(__DIR__.'/../../../../../web/images'));
            $mpdf->shrink_tables_to_fit = 0;
            $mpdf->WriteHTML($content_html);

            $pdf = $mpdf->Output('', 'S');

            $response = new Response();

            if($this->in->getBool('html')) {
                $response->setContent($content_html);
            }
            else
            {
                $response->setContent($pdf);
                $response->headers->set('Content-Disposition', 'attachment; filename=Ticket_'.$ticket->id.'.pdf');
                $response->headers->set('Content-Type', 'application/pdf');
            }

            return $response;
        }

		return $this->render($tpl, $vars);
	}

	protected function _getTicketPerms($ticket)
	{
		$ticket_perms = array();
		$ticket_perms['delete'] = $this->person->PermissionsManager->TicketChecker->canDelete($ticket);
		$ticket_perms['reply'] = $this->person->PermissionsManager->TicketChecker->canReply($ticket);
		$ticket_perms['modify_set_closed'] = $this->person->PermissionsManager->TicketChecker->canSetClosed($ticket);

		foreach (array('department', 'fields', 'assign_agent', 'assign_team', 'assign_self', 'cc', 'merge', 'labels', 'notes', 'set_hold', 'set_awaiting_agent', 'set_awaiting_user', 'set_resolved') as $p) {
			$ticket_perms["modify_$p"] = $this->person->PermissionsManager->TicketChecker->canModify($ticket, $p);
		}

		return $ticket_perms;
	}

	protected function _getMessageBlockInfo($ticket, $since_message_id = 0, $since_log_id = 0, array $ticket_attachments = null, $is_pdf = false)
	{
		$message_count = 0;
		$note_count = 0;

		$ticket_messages = $this->em->getRepository('DeskPRO:TicketMessage')->getTicketMessages(
			$ticket,
			array('since_id' => $since_message_id, 'with_notes' => true)
		);

		if ($ticket_attachments === null) {
			$ticket_attachments = $this->em->getRepository('DeskPRO:TicketAttachment')->getAttachmentsForMessages($ticket_messages);
		}

		// Group attachments into messages so we can place them into each message
		$ticket_message_attachments = array();
		foreach ($ticket_attachments as $attach) {
			if (!$attach['message']) continue;
			if (!isset($ticket_message_attachments[$attach['message']['id']])) {
				$ticket_message_attachments[$attach['message']->getId()] = array();
			}

			$ticket_message_attachments[$attach['message']->getId()][] = $attach->getId();
		}

		$ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->getLogsForTicket(
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
		// - $ticket_message_logs[after] is an array of remaining log items (ie after last message)
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

		$all_feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedbackForTicket($ticket);

		if ($ticket_messages) {
            if($is_pdf) {
                $tpl = 'DeskPRO:pdf_agent:ticket-messages-batch.html.twig';
            }
            else {
                $tpl = 'AgentBundle:Ticket:ticket-messages-batch.html.twig';
            }
			$ticket_messages_block = $this->renderView($tpl, array(
				'ticket' => $ticket,
				'ticket_messages' => $ticket_messages,
				'ticket_message_attachments' => $ticket_message_attachments,
				'ticket_attachments' => $ticket_attachments,
				'ticket_message_logs' => $ticket_message_logs,
				'ticket_logs' => $ticket_logs,
				'all_feedback' => $all_feedback,
			));
		}

		$ticket_messages_blockcache = array(
			'ticket_messages_block' => $ticket_messages_block,
			'ticket_messages' => $ticket_messages,
			'ticket_attachments' => $ticket_attachments,
			'ticket_message_attachments' => $ticket_message_attachments,
			'message_count' => $message_count,
			'note_count' => $note_count,
			'last_message_id' => $last_message_id,
			'last_log_id' => $last_log_id,
		);

		return $ticket_messages_blockcache;
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
			$message = $this->em->getRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($ticket);
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

		if (!$person && $this->in->getUint('person_id')) {
			$person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));
		}

		$ticket_snippets = $this->em->getRepository('DeskPRO:TicketSnippet')->getSnippetsForAgent($this->person);
		$ticket_snippet_cats = $this->em->getRepository('DeskPRO:TicketSnippetCategory')->getCatsForAgent($this->person);

		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

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
		$cat = $this->em->find('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));

		return $this->render('AgentBundle:Ticket:ticket-snippets-editcat.html.twig', array(
			'category' => $cat,
		));
	}

	public function saveSnippetCatAction()
	{
		$cat = $this->em->find('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));
		$cat['title'] = $this->in->getString('title');

		$this->em->persist($cat);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'category_id' => $cat['id'],
			'title' => $cat['title']
		 ));
	}

	public function deleteSnippetCatAction()
	{
		$cat = $this->em->find('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));

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
			$snippet = $this->em->find('DeskPRO:TicketSnippet', $this->in->getUint('snippet_id'));
			$category = $snippet->category;
		} else {
			$category = $this->em->find('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));
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
		$snippet = $this->em->find('DeskPRO:TicketSnippet', $this->in->getUint('snippet_id'));

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
		$ticket = $this->getTicketOr404($ticket_id, 'fields');

		$this->em->beginTransaction();

		try {
			$field_manager = $this->container->getSystemService('ticket_fields_manager');
			$post_custom_fields = $this->request->request->get('custom_fields', array());
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $org);
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		$custom_fields = $field_manager->getDisplayArrayForObject($org);


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
		$ticket = $this->getTicketOr404($ticket_id, 'edit');

		$tcheck = $this->person->PermissionsManager->TicketChecker;

		if ($this->in->checkIsset('department') && $tcheck->canModify($ticket, 'department')) {
			$ticket['department_id'] = $this->in->getUint('department');
		}

		if ($tcheck->canModify($ticket, 'fields')) {
			if ($this->in->checkIsset('category')) {
				$ticket['category_id'] = $this->in->getUint('category');
			}
			if ($this->in->checkIsset('product')) {
				$ticket['product_id'] = $this->in->getUint('product');
			}
			if ($this->in->checkIsset('priority')) {
				$ticket['priority_id'] = $this->in->getUint('priority');
			}
		}

		if ($this->in->checkIsset('status')) {
			$status = $this->in->checkIsset('status');
			if ($status == 'resolved' && !$tcheck->canModify($ticket, 'set_resolved')) {
				$status = null;
			}
			if ($status == 'awaiting_agent' && !$tcheck->canModify($ticket, 'set_awaiting_agent')) {
				$status = null;
			}
			if ($status == 'awaiting_user' && !$tcheck->canModify($ticket, 'set_awaiting_user')) {
				$status = null;
			}
			if ($status) {
				$ticket['status'] = $this->in->getString('status');
			}
		}

		if ($this->in->checkIsset('agent')) {
			$agent = $this->in->checkIsset('agent');
			if ($agent == $this->person->id && !$tcheck->canModify($ticket, 'assign_self')) {
				$agent = null;
			} elseif (!$tcheck->canModify($ticket, 'assign_agent')) {
				$agent = null;
			}

			if ($agent) {
				$ticket['agent_id'] = $this->in->getUint('agent');
			}
		}
		if ($this->in->checkIsset('agent_team')) {
			$team = $this->in->checkIsset('agent_team');
			if ($this->person->Agent->isTeamMember($team) && !$tcheck->canModify($ticket, 'assign_self')) {
				$team = null;
			} elseif (!$tcheck->canModify($ticket, 'assign_team')) {
				$team = null;
			}

			if ($team) {
				$ticket['agent_team_id'] = $this->in->getUint('agent_team');
			}
		}

		$this->db->beginTransaction();
		try {
			$this->em->persist($ticket);
			$this->em->flush();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# add-participant
	############################################################################

	public function addParticipantAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_cc');
        $ticket_perms = $this->_getTicketPerms($ticket);

		$person = null;
		if ($this->in->getUint('person_id')) {
			$person = $this->em->find('DeskPRO:Person', $this->in->getUint('perosn_id'));
		} elseif ($email_address = $this->in->getString('email_address')) {
			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_address);

			if (!$person) {
				$person = new Person();
				$person->setEmail($email_address);
			}
		}

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($person->id) {
			if ($ticket->hasParticipantPerson($person)) {
				return $this->render('AgentBundle:Ticket:view-user-cc-row.html.twig', array('person' => $person, 'ticket_perms' => $ticket_perms));
			}
		}

		$this->db->beginTransaction();

		try {

			if (!$person->id) {
				$this->em->persist($person);
				$this->em->flush();
			}

			$part = $ticket->addParticipantPerson($person);
			$this->em->persist($part);
			$this->em->persist($ticket);
			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->render('AgentBundle:Ticket:view-user-cc-row.html.twig', array('person' => $person, 'ticket_perms' => $ticket_perms));
	}

	############################################################################
	# remove-participant
	############################################################################

	public function removeParticipantAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_cc');
		$person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->db->beginTransaction();

		try {
			$ticket->removeParticipantPerson($person);
			$this->em->persist($ticket);
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
            return $this->createJsonResponse(array('success' => false));
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function setAgentParticipantsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_assign_agent');

		$agents = $this->em->getRepository('DeskPRO:Person')->getPeopleFromIds($this->in->getCleanValueArray('agent_part_ids', 'uint', 'discard'));

		$this->db->beginTransaction();

		try {
			$ticket->setAgentParticipants($agents);
			$ticket->getTicketLogger()->done();
			$this->em->persist($ticket);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('sucess' => true));
	}


	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_labels');

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$ticket->getLabelManager()->setLabelsArray($labels);

		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# ajax-save-reply
	############################################################################

	public function ajaxSaveReplyAction($ticket_id)
	{

		if (!$this->in->getString('message') || $this->in->getString('message') == trim($this->person->getPref('agent.ticket_signature'))) {
			return $this->createJsonResponse(array('error' => 'no_message'));
		}

		$ticket = $this->getTicketOr404($ticket_id, 'reply');

		#------------------------------
		# Handle new message
		#------------------------------

		$draft_pref = $this->em->getRepository('DeskPRO:PersonPref')->find(array(
			'person' => $this->person['id'],
			'name' => "ticket_draft.{$ticket['id']}"
		));

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['ip_address'] = $this->request->getClientIp();
		$message->setMessageText($this->in->getString('message'));

		if ($this->in->getBool('options.is_note')) {
			$message['is_agent_note'] = true;
		}

		foreach ($this->in->getCleanValueArray('attach') as $blob_id) {

			$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$message->addAttachment($attach);
		}

		if ($dupe_message = $this->em->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $ticket)) {
			return $this->createJsonResponse(array(
				'dupe_message' => true,
				'message_id' => $dupe_message['id'],
				'time' => $dupe_message->date_created->getTimestamp()
			));
		} else {
			$ticket->addMessage($message);

			if (!$this->in->getBool('options.notify_user')) {
				$ticket->getTicketLogger()->recordExtra('suppress_user_notify', true);
			}
		}

		// havent persisted the messag yet, it was just for dupe checking

		#------------------------------
		# Handle CC'ing/parts
		#------------------------------

		$add_parts = array();
		$rem_parts = array();
		$changed_parts = false;

		$email_validator = new \Orb\Validator\StringEmail();

		$current_user_ids = array();
		$current_agent_ids = array();
		foreach ($ticket->participants as $p) {
			if ($p->person->is_agent) {
				$current_agent_ids[] = $p->person->id;
			} else {
				$current_user_ids[] = $p->person->id;
			}
		}

		// People cc emails
		$user_parts_emails = $this->in->getString('user_parts');
		$user_parts_emails = explode(',', $user_parts_emails);

		$got_user_ids = array();
		$new_user_ids = array();
		foreach ($user_parts_emails as $email) {
			if (!$email || !$email_validator->isValid($email)) {
				continue;
			}

			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if ($person) {
				$got_user_ids[] = $person->id;
			} else {
				$person = Person::newContactPerson(array('email' => $email));
				$this->em->persist($person);
				$this->em->flush();
				$new_user_ids[] = $person->id;
			}

			$changed_parts = true;
			$add_parts[] = $person;
		}

		$remove_user_ids = array_diff($current_user_ids, $got_user_ids);
		foreach ($remove_user_ids as $id) {
			$changed_parts = true;
			$rem_parts[] = $id;
		}

		$got_agent_ids = $this->in->getCleanValueArray('agent_parts', 'string', 'discard');
		foreach ($got_agent_ids as $id) {
			$changed_parts = true;
			$add_parts[] = $this->em->find('DeskPRO:Person', $id);
		}

		$remove_agent_ids = array_diff($current_agent_ids, $got_agent_ids);
		foreach ($remove_agent_ids as $id) {
			$changed_parts = true;
			$rem_parts[] = $id;
		}

		if ($new_user_ids) {
			$tracker = $ticket->getTicketLogger();
			$tracker->recordExtra('enabled_cc', $new_user_ids);
		}

		#------------------------------
		# Save
		#------------------------------

		$this->db->beginTransaction();

		try {

			if ($add_parts) {
				foreach ($add_parts as $p) {
					$ticket->addParticipantPerson($p);
				}
			}
			if ($rem_parts) {
				foreach ($rem_parts as $pid) {
					$ticket->removeParticipantPerson($pid);
				}
			}

			#------------------------------
			# Handle actions
			#------------------------------

			if (!$message['is_agent_note']) {
				$ticket['agent_id']      = $this->in->getUint('options.agent_id');
				$ticket['agent_team_id'] = $this->in->getUint('options.agent_team_id');
				$ticket['status']        = $this->in->getString('options.status');

				if ($this->in->getBool('options.do_kbpending')) {
					$kb_pending = new ArticlePendingCreate();
					$kb_pending->fromArray(array(
						'person' => $this->person,
						'ticket' => $ticket,
						'message' => $message
					));
					$this->em->persist($kb_pending);
				}
			}

			// Delete any possible ticket draft
			if ($draft_pref) {
				$this->em->remove($draft_pref);
			}

			$this->em->persist($ticket);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		if (!$message['is_agent_note']) {
			$participants = $this->em->createQuery("
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

		$client_messages = false;
		if ($this->in->getUint('client_messages_since')) {
			$client_messages = $this->em->getRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		$close_tab = $this->in->getBool('options.close_tab');

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$message->id - 1,
			$this->in->getUint('last_log_id')
		);

		// New reply box
		$participants = $this->em->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		$participant_ids = array();
		$agent_parts = array();
		$user_parts = array();

		foreach ($participants as $p) {
			$participant_ids[] = $p->person->id;
			if ($p->person->is_agent) {
				$agent_parts[] = $p;
			} else {
				$user_parts[] = $p;
			}
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

		$replybox = $this->renderView('AgentBundle:Ticket:replybox.html.twig', array(
			'agents' => $agents,
			'agent_teams' => $agent_teams,
			'ticket' => $ticket,
			'participants' => $participants,
			'participant_ids' => $participant_ids,
			'agent_parts' => $agent_parts,
			'user_parts' => $user_parts,
			'agent_signature' => $this->person->getPref('agent.ticket_signature'),
			'ticket_perms' => $this->_getTicketPerms($ticket),
		));

		$data = array_merge($data, array(
			'updated_agent_parts_html' => isset($updated_agent_parts) ? $updated_agent_parts : '',
			'updated_agent_parts_html_count' => isset($updated_agent_parts_count) ? $updated_agent_parts_count : null,
			'replybox_html' => $replybox,
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

	public function updateViewsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$this->in->getUint('last_message_id'),
			$this->in->getUint('last_log_id')
		);

		// New reply box
		$participants = $this->em->createQuery("
			SELECT p
			FROM DeskPRO:TicketParticipant p
			LEFT JOIN p.person person
			LEFT JOIN p.person_email person_email
			WHERE p.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		$participant_ids = array();
		$agent_parts = array();
		$user_parts = array();

		foreach ($participants as $p) {
			$participant_ids[] = $p->person->id;
			if ($p->person->is_agent) {
				$agent_parts[] = $p;
			} else {
				$user_parts[] = $p;
			}
		}


		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

		$replybox = $this->renderView('AgentBundle:Ticket:replybox.html.twig', array(
			'agents' => $agents,
			'agent_teams' => $agent_teams,
			'ticket' => $ticket,
			'participants' => $participants,
			'participant_ids' => $participant_ids,
			'agent_parts' => $agent_parts,
			'user_parts' => $user_parts,
			'agent_signature' => $this->person->getPref('agent.ticket_signature'),
			'ticket_perms' => $this->_getTicketPerms($ticket),
		));

		$data = array_merge($data, array(
			'updated_agent_parts_html' => 1,
			'updated_agent_parts_html_count' => 1,
			'replybox_html' => $replybox,
			'agent_id' => $ticket['agent_id'],
			'agent_team_id' => $ticket['agent_team_id'],
			'status' => $ticket['status'],
			'close_tab' => false,
		));

		return $this->createJsonResponse($data);
	}


	############################################################################
	# ajax-save-actions
	############################################################################

	public function ajaxSaveActionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify');
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

		$this->em->beginTransaction();

		try {
			$macro_id = $this->in->getUint('macro_id');
			if ($macro_id) {
				$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);
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

			if ($this->in->getBool('with_set_agent_parts')) {
				$agents = $this->em->getRepository('DeskPRO:Person')->getPeopleFromIds($this->in->getCleanValueArray('set_agent_part_ids', 'uint', 'discard'));
				$ticket->setAgentParticipants($agents);
			}

			$ticket_edit->save();
			$this->em->flush();

			$field_manager = $this->container->getSystemService('ticket_fields_manager');
			if ($this->person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {

				if (!empty($_POST['custom_fields'])) {
					$post_custom_fields = $this->request->request->get('custom_fields', array());
					if (!empty($post_custom_fields)) {
						$field_manager->saveFormToObject($post_custom_fields, $ticket);
						$this->em->persist($ticket);
					}

					$this->em->flush();
				}
			}

			$ticket->getTicketLogger()->done();

			$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		$data = array('data' => array());
		if (isset($result['new_reply'])) {
			$data['data']['new_reply'] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $result['new_reply']
			));
		}

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$data['holders'] = $this->renderView('AgentBundle:Ticket:view-page-display-holders.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields
		));

		$client_messages = false;
		if ($this->in->getUint('client_messages_since')) {
			$client_messages = $this->em->getRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		if ($client_messages) {
			$data['client_messages'] = $client_messages;
		}

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-get-macro-actions
	############################################################################

	public function ajaxGetMacroAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$macro_id = $this->in->getUint('macro_id');
		$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);

		$actions_collection = $macro->getActionsCollection($ticket);
		$actions = $actions_collection->getApplyActions($ticket, $this->person);

		return $this->createJsonResponse(array(
			'macro_id' => $macro->id,
			'actions_apply' => $actions,
			'actions_display' => $macro->actions
		));
	}

	############################################################################
	# view-message-details
	############################################################################

	public function viewMessageDetailsAction($message_id)
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);
		$ticket = $message->ticket;

		return $this->render('AgentBundle:Ticket:message-details.html.twig', array(
			'message' => $message,
			'ticket' => $ticket
		));
	}

	public function viewUnformattedMessageAction($message_id)
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

		return $this->render('AgentBundle:Ticket:message-details-unformatted.html.twig', array(
			'message' => $message,
			'ticket' => $message['ticket']
		));
	}

	public function viewEmailSourceAction($message_id)
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

		return $this->render('AgentBundle:Ticket:message-details-email-source.html.twig', array(
			'message' => $message,
			'ticket' => $message['ticket']
		));
	}

	public function ajaxGetMessageQuoteAction($message_id)
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

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

		$messages = $this->em->createQuery("
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
		$ticket = $this->getTicketOr404($ticket_id, 'modify_assign_agent');

		$set_agent_ids = $this->in->getCleanValueArray('person_ids', 'uint', 'discard');
		$ticket->setParticipantAgentIds($set_agent_ids);

		$this->em->transactional(function($em) use ($ticket) {
			$em->persist($ticket);
			$em->flush();
		});

		$participants = $this->em->createQuery("
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
		$ticket = $this->getTicketOr404($ticket_id, 'delete');

		if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$ticket->setStatus('hidden.deleted');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		$this->db->insert('tickets_deleted', array(
			'ticket_id' => $ticket->id,
			'by_person_id' => $this->person->id,
			'new_ticket_id' => 0,
			'reason' => $this->in->getString('reason'),
			'date_created' => date('Y-m-d H:i:s')
		));

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	/**
	 * Spam a ticket
	 *
	 * @param  $ticket_id
	 */
	public function spamAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'delete');

		$this->em->getConnection()->beginTransaction();

		try {
			$ticket->setStatus('hidden.spam');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true
		));
	}


	############################################################################
	# merge
	############################################################################

	public function mergeOverlayAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

		$tickets_by_user = $this->em->getRepository('DeskPRO:Ticket')->getLatestByUser($ticket->person);
		$open_tickets    = $this->em->getRepository('DeskPRO:Ticket')->getTicketsFromIds($this->in->getCleanValueArray('open_ticket_ids', 'uint', 'discard'));

		$fn = function ($t) use ($ticket) {
			if ($t['id'] == $ticket['id'] or $t->person->getId() != $ticket->person->getId()) {
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
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');
		$other_ticket = $this->getTicketOr404($other_ticket_id, 'modify_merge');

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

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

		#------------------------------
		# Custom fields
		#------------------------------

		$ticket = new \Application\DeskPRO\Entity\Ticket();
		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		return $this->render('AgentBundle:Ticket:newticket.html.twig', array(
			'agents' => $agents,
			'agent_teams' => $agent_teams,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
		));
	}

	public function newSaveAction()
	{
		if (!$this->person->hasPerm('agent_tickets.create')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$newticket = new \Application\AgentBundle\Form\Model\NewTicket($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewTicket();
		$form = $this->get('form.factory')->create($formType, $newticket);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			#------------------------------
			# Validate
			#------------------------------

			$errors = array();

			// Person
			$person_id = $this->in->getUint('newticket.person.id');
			if ($person_id) {
				$check_person = $this->em->find('DeskPRO:Person', $person_id);
				if (!$check_person) {
					$errors['person_id'] = true;
				}
			} else {
				$new_email = $this->in->getString('newticket.person.email_address');
				if (!$new_email && !$this->in->getString('newticket.person.name')) {
					$errors['person_no_user'] = true;
				} elseif (!\Orb\Validator\StringEmail::isValueValid($new_email)) {
					$errors['person_email_address'] = true;
				}
			}

			if (!$this->in->getString('newticket.subject')) {
				$errors['subject'] = true;
			}
			if (!$this->in->getString('newticket.message')) {
				$errors['message'] = true;
			}

			if ($errors) {
				$errors = array_keys($errors);
				return $this->createJsonResponse(array('error' => true, 'error_codes' => $errors));
			}

			#------------------------------
			# Save
			#------------------------------

			$this->db->beginTransaction();

			try {
				$newticket->save();
				$ticket = $newticket->getTicket();

				$field_manager = $this->container->getSystemService('ticket_fields_manager');
				$post_custom_fields = $this->request->request->get('custom_fields', array());
				if (!empty($post_custom_fields)) {
					$field_manager->saveFormToObject($post_custom_fields, $ticket);
				}

				$this->em->flush();

				#------------------------------
				# Labels
				#------------------------------

				$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');
				if ($labels) {
					$ticket->getLabelManager()->setLabelsArray($labels);
					$this->em->flush();
				}

				#------------------------------
				# Add CC's
				#------------------------------

				$add_cc_people = $this->container->getIn()->getCleanValueArray('add_cc_person', 'uint');
				if ($add_cc_people) {
					foreach ($add_cc_people as $pid) {
						$p = $this->em->find('DeskPRO:Person', $pid);
						if ($p) {
							$part = $ticket->addParticipantPerson($p);
							$this->em->persist($part);
							$this->em->persist($ticket);
						}
					}

					$this->em->flush();
				}

				$add_cc_people = $this->container->getIn()->getCleanValueArray('add_cc_person', 'uint');
				if ($add_cc_people) {
					foreach ($add_cc_people as $pid) {
						$p = $this->em->find('DeskPRO:Person', $pid);
						if ($p) {
							$part = $ticket->addParticipantPerson($p);
							$this->em->persist($part);
						}
					}

					$this->em->persist($ticket);
					$this->em->flush();
				}

				$new_cc_people_ids = array_merge(
					array_keys($this->container->getIn()->getCleanValueArray('new_cc_person_name', 'raw', 'string')),
					array_keys($this->container->getIn()->getCleanValueArray('new_cc_person_email', 'raw', 'string'))
				);
				$new_cc_people_ids = array_unique($new_cc_people_ids);

				if ($new_cc_people_ids) {
					foreach ($new_cc_people_ids as $fid) {
						$email = $this->container->getIn()->getCleanValue('new_cc_person_email.'.$fid, 'string');
						$name  = $this->container->getIn()->getCleanValue('new_cc_person_name.'.$fid, 'string');

						if (!$email && !$name) {
							continue;
						}
						if ($email && !\Orb\Validator\StringEmail::isValueValid($email)) {
							continue;
						}

						$p = Person::newContactPerson(array(
							'name' => $name,
							'email' => $email
						));
						$this->em->persist($p);
						$this->em->flush();

						$part = $ticket->addParticipantPerson($p);
						$this->em->persist($part);
					}

					$this->em->flush();
				}

				#------------------------------
				# Related chat
				#------------------------------

				$chat_id = $this->in->getUint('for_chat_id');
				$chat = null;
				if ($chat_id) {
					$chat = $this->em->find('DeskPRO:ChatConversation', $chat_id);

					$ticket->linked_chat = $chat;
					$this->em->persist($ticket);
					$this->em->flush();
				}

				#------------------------------
				# Related comment
				#------------------------------

				$comment_type   = $this->in->getString('for_comment_type');
				$comment_id     = $this->in->getUint('for_comment_id');
				$comment_action = $this->in->getString('comment_action');

				if ($comment_id && $comment_type && $comment_action) {
					$entity = $this->_getCommentEntityName($comment_type);
					$comment = $this->em->find($entity, $comment_id);

					switch ($comment_action) {
						case 'delete':
							$comment->setStatus('deleted');
							break;
						case 'approve':
							$comment->setStatus('visible');
							break;
					}

					$this->em->persist($comment);
					$this->em->flush();
				}

				$ticket->recomputeHash();
				if ($dupe_ticket = $this->em->getRepository('DeskPRO:Ticket')->checkDupeTicket($ticket)) {
					$e = new \Application\DeskPRO\Tickets\DuplicateTicketException();
					$e->ticket_id = $dupe_ticket->id;
					throw $e;
				}

				$this->db->commit();
			} catch (\Application\DeskPRO\Tickets\DuplicateTicketException $e) {
				$this->db->rollback();
				return $this->createJsonResponse(array(
					'error' => true,
					'is_dupe' => true,
					'dupe_ticket_id' => $e->ticket_id
				));
			} catch (\Exception $e) {
				$this->db->rollback();
				throw $e;
			}

			return $this->createJsonResponse(array(
				'success' => true,
				'ticket_id' => $ticket['id'],
				'comment_id' => $comment_id,
				'comment_type' => $comment_type
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}

	protected function _getCommentEntityName($typename)
	{
		switch ($typename) {
			case 'articles':
				return 'DeskPRO:ArticleComment';
			case 'downloads':
				return 'DeskPRO:DownloadComment';
			case 'news':
				return 'DeskPRO:NewsComment';
			case 'feedback':
				return 'DeskPRO:FeedbackComment';
		}
	}

	public function newticketGetPersonRowAction($person_id)
	{
		if (!$person_id && $this->in->getUint('person_id')) {
			$person_id = $this->in->getUint('person_id');
		}

		$person = false;
		if ($person_id) {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		}
		if (!$person && $this->in->getString('email')) {
			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('email'));
		}

		$session = null;
		if ($this->in->getUint('session_id')) {
			$session = $this->em->find('DeskPRO:Session', $this->in->getUint('session_id'));
		}
		if ($session && $session->person) {
			$person = $session;
		}

		if (!$person) {
			$person = new Person();
			if ($session) {
				$person->name = $session->visitor->name;
				if ($session->visitor->email) {
					$person->setEmail($session->visitor->email);
				}
			}
		}

		return $this->render('AgentBundle:Ticket:newticket-person-row.html.twig', array(
			'person' => $person
		));
	}

	############################################################################

	public function checkPerm($ticket, $check_perm)
	{
		$fail = false;
		if (strpos($check_perm, 'modify_') === 0) {
			$check_perm = str_replace('modify_', '', $check_perm);
			if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, $check_perm)) {
				$fail = true;
			}
		} elseif ($check_perm == 'delete') {
			if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
				$fail = true;
			}
		} elseif ($check_perm == 'reply') {
			if (!$this->person->PermissionsManager->TicketChecker->canReply($ticket)) {
				$fail = true;
			}
		}

		if ($fail) {
			return false;
		}

		return true;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id, $check_perm = null)
	{
		$q = $this->em->createQuery("SELECT t FROM DeskPRO:Ticket t WHERE t.id = ?0");
		$q->setFetchMode('DeskPRO:Person', 'person', 'EAGER');
		$q->setFetchMode('DeskPRO:Person', 'agent', 'EAGER');
		$q->setParameters(array($ticket_id));

		$ticket = $q->getOneOrNullResult();

		if (!$ticket || !$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		if ($check_perm && !$this->checkPerm($ticket, $check_perm)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}
