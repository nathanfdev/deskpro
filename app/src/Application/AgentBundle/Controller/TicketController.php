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

use Application\AgentBundle\Form\Model\NewTicket;
use Application\AgentBundle\Validator\NewTicketValidator;
use Application\DeskPRO\App;
use Application\DeskPRO\Debug\Data\TicketData;
use Application\DeskPRO\Debug\Data\TicketFilterData;
use Application\DeskPRO\Debug\Data\TicketLogsData;
use Application\DeskPRO\Debug\Data\TicketPersonData;
use Application\DeskPRO\Debug\Data\TicketTriggerData;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\EventDispatcher\PropertyChangedCallback;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\AgentAction;
use Application\DeskPRO\Tickets\TicketActions\AgentTeamAction;
use Application\DeskPRO\Tickets\TicketActions\ReplyAction;
use Application\DeskPRO\Tickets\TicketActions\ReplySnippetAction;
use Application\DeskPRO\Tickets\TicketActions\StatusAction;
use Application\DeskPRO\Tickets\TicketMerge\TicketMerge;
use Application\DeskPRO\Tickets\TicketSplit;
use DeskPRO\Kernel\KernelErrorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Orb\Util\Dates;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles ticket searches
 */
class TicketController extends AbstractController
{
	public function requireRequestToken($action, $arguments = null)
	{
		if ($action == 'viewRawMessageAction') {
			return false;
		}

		return parent::requireRequestToken($action, $arguments);
	}

	############################################################################
	# view
	############################################################################

	public function viewAction($ticket_id)
	{
        $is_pdf = $this->in->getBool('pdf');
		$is_print = $this->in->getBool('view_print');

		try	{
			$ticket = $this->getTicketOr404($ticket_id);
		} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			// try to find a delete log
			$delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
			if ($delete_log) {
				return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
			} else {
				throw $e;
			}
		}

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

		$ticket_messages_blockcache = $this->_getMessageBlockInfo($ticket, 1, $ticket_attachments, $is_pdf, $is_print);
		$ticket_messages_block = $ticket_messages_blockcache['ticket_messages_block'];
		$ticket_attachments = $ticket_messages_blockcache['ticket_attachments'];
		$ticket_message_attachments = isset($ticket_messages_blockcache['ticket_message_attachments']) ? $ticket_messages_blockcache['ticket_message_attachments'] : array();
		$counts['messages'] = $ticket_messages_blockcache['message_count'];

		$ticket_flagged = $this->em->getRepository('DeskPRO:TicketFlagged')->getFlagForTicket($ticket, $this->person);

		$macros = $this->person->Agent->getMacros();

		$tpl = 'AgentBundle:Ticket:view.html.twig';

		$hidden_data = $this->_getHiddenBarData($ticket);

		// Check if the search adapter
		$show_related_content = false;

		$participants = $ticket->participants;

		$participant_ids = array();
		$agent_parts = array();
		$user_parts = array();

		foreach ($participants as $p) {
			$participant_ids[$p->person->getId()] = $p->person->id;
			if ($p->person->is_agent) {
				$agent_parts[$p->person->getId()] = $p;
			} else {
				$user_parts[$p->person->getId()] = $p;
			}
		}

		$agents = $this->container->getAgentData()->getAgents();
		$agent_teams = $this->container->getDataService('AgentTeam')->getTeams();

		#------------------------------
		# Linked tasks
		#------------------------------

		$tasks = $this->em->getRepository('DeskPRO:Task')->findLinkedTicketTasks($ticket, $this->person, true);
		usort($tasks, function($a, $b) {
			$a_time = $a->date_due ? $a->date_due->getTimestamp() : 0;
			$b_time = $b->date_due ? $b->date_due->getTimestamp() : 0;

			if ($a_time == $b_time) {
				return 0;
			}

			return ($a_time < $b_time) ? -1 : 1;
		});

		$addable_slas = $this->em->getRepository('DeskPRO:Sla')->getAddableSlas($ticket);
		$ticket_api = array();
		foreach (array(
			'id', 'subject', 'ref', 'status', 'hidden_status', 'creation_system', 'is_hold',
			'urgency', 'total_user_waiting', 'total_to_first_reply'
		) AS $key) {
			$ticket_api[$key] = $ticket->$key;
		}

		foreach (array(
			'date_created', 'date_resolved', 'date_closed', 'date_first_agent_assign',
			'date_first_agent_reply', 'date_last_agent_reply', 'date_last_user_reply',
			'date_agent_waiting', 'date_user_waiting', 'date_status', 'date_locked'
		) AS $date_key) {
			if ($ticket->$date_key instanceof \DateTime) {
				$ticket_api[$date_key] = $ticket->$date_key->getTimestamp();
			}
		}

		$ticket_api['person'] = $ticket->person->getDataForWidget();

		if ($ticket->agent) {
			$ticket_api['agent'] = $ticket->agent->getDataForWidget();
		}

		foreach (array(
			'department' => 'title',
			'language' => 'title',
			'category' => 'title',
			'priority' => 'title',
			'workflow' => 'title',
			'organization' => 'name'
		) AS $key => $title_field) {
			if ($ticket->$key) {
				$ticket_api[$key] = array('id' => $ticket->$key->id, $title_field => $ticket->$key->$title_field);
			}
		}
		if ($ticket->product) {
			$ticket_api['product'] = $ticket->product->toApiData();
		}
		if (count($ticket->labels)) {
			$ticket_api['labels'] = array();
			foreach ($ticket->labels AS $label) {
				$ticket_api['labels'][] = $label['label'];
			}
		}

		foreach ($custom_fields AS $field) {
			$ticket_api['custom'][$field['id']] = array(
				'id' => $field['id'],
				'title' => $field['title'],
				'value' => isset($field['value']['value']) ? $field['value']['value'] : false
			);
		}

		$draft = $this->em->getRepository('DeskPRO:Draft')->getDraft('ticket', $ticket->id);
		if ($draft && !empty($draft->extras['attach'])) {
			$draft_attachments = $this->em->getRepository('DeskPRO:Blob')->getByIds($draft->extras['attach'], true);
		} else {
			$draft_attachments = array();
		}

		$active_drafts = $this->em->getRepository('DeskPRO:Draft')->getActiveDrafts('ticket', $ticket->id);
		unset($active_drafts[$this->person->id]);

		if (App::getSetting('core_tickets.lock_on_view') && !$ticket->hasLock()) {
			$ticket->setLockedByAgent($this->person);

			$this->em->persist($ticket);
			$this->em->flush();
		}

		$edit_person = $this->person->hasPerm('agent_people.edit');
		if ($edit_person) {
			if (!$this->person->can_admin && $ticket->person->is_agent && $ticket->person->getId() != $this->person->getId()) {
				$edit_person = false;
			}
		}

		$agent_map = array();
		foreach ($agents AS $agent) {
			$agent_map[$agent->getId()] = array(
				'name' => $agent->getDisplayName(),
				'picture_url' => $agent->getPictureUrl(20)
			);
		}
		unset($agent_map[$this->person->getId()]);

		#------------------------------
		# Validate a ticket to see if we need to lock the reply form
		#------------------------------

		$newticket = new NewTicket($this->em, $this->person);
		$newticket->setValuesFromTicket($ticket);

		$validator = new NewTicketValidator();
		$layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($newticket->department_id ?: 0);
		$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::NEW_TICKET);
		$validator->setLayout($layout);

		$validator_errors = array();
		if (!$validator->isValid($newticket)) {
			foreach ($validator->getErrorsInfo() as $info) {
				$validator_errors[] = htmlspecialchars($info['message']);
			}
		}

		#------------------------------
		# Linked tickets
		#------------------------------

		$linked_tickets = array(
			'parent'   => null,
			'siblings' => array(),
			'children' => array(),
			'count'    => 0
		);

		if ($ticket->parent_ticket && $ticket->parent_ticket->status != 'hidden' && $this->checkPerm($ticket->parent_ticket, 'view')) {
			$linked_tickets['parent'] = $ticket->parent_ticket;

			// Find siblings
			$linked_tickets['siblings'] = $this->permCheckArray(
				$this->em->getRepository('DeskPRO:Ticket')->getLinkedTickets($ticket->parent_ticket),
				'view'
			);
			$linked_tickets['siblings'] = array_filter($linked_tickets['siblings'], function($t) use ($ticket) {
				if ($t->id == $ticket->id) {
					return false;
				} else {
					return true;
				}
			});
		}

		$linked_tickets['children'] = $this->permCheckArray(
			$this->em->getRepository('DeskPRO:Ticket')->getLinkedTickets($ticket),
			'view'
		);

		$linked_tickets['count'] = array_sum(array(
			$linked_tickets['parent'] ? 1 : 0,
			count($linked_tickets['siblings']),
			count($linked_tickets['children'])
		));

		#------------------------------
		# Pre-load person and org
		#------------------------------

		$logs_block_info = $this->_getTicketLogsBlockInfo($ticket);

		$agents_with_perm = array();
		foreach ($this->container->getAgentData()->getAgents() as $agent) {
			$agent->loadHelper('AgentPermissions');
			$agents_with_perm[$agent->id] = $agent->PermissionsManager->TicketChecker->canView($ticket);
		}

		$vars = array(
			'agents'                     => $agents,
			'agents_with_perm'           => $agents_with_perm,
			'agent_teams'                => $agent_teams,
			'agent_map'                  => $agent_map,
			'tasks'                      => $tasks,

			'ticket_perms'               => $this->_getTicketPerms($ticket),
			'ticket'                     => $ticket,
			'ticket_api'                 => $ticket_api,
			'ticket_attachments'         => $ticket_attachments,
			'ticket_message_attachments' => $ticket_message_attachments,

			'validator_errors'           => $validator_errors,

			'draft'                      => $draft,
			'draft_attachments'          => $draft_attachments,
			'active_drafts'              => $active_drafts,

			'edit_person'                => $edit_person,

			'last_message_id'            => $ticket_messages_blockcache['last_message_id'],
			'message_count'              => $ticket_messages_blockcache['message_count'],
			'message_page_count'         => $ticket_messages_blockcache['message_page_count'],
			'message_page'               => $ticket_messages_blockcache['message_page'],

			'participants'               => $participants,
			'participant_ids'            => $participant_ids,
			'agent_parts'                => $agent_parts,
			'user_parts'                 => $user_parts,

			'custom_fields'              => $custom_fields,

			'show_related_content'       => $show_related_content,
			'linked_tickets'             => $linked_tickets,

			'ticket_messages_block'      => $ticket_messages_block,
			'logs_block'                 => $logs_block_info['rendered'],

			'ticket_deleted'             => $hidden_data['ticket_deleted'],
			'hard_delete_time'           => $hidden_data['hard_delete_time'],
			'ticket_options'             => $ticket_options,
			'ticket_flagged'             => $ticket_flagged,
			'macros'                     => $macros,

			'agent_signature'            => $this->person->getSignature(),
			'agent_signature_html'       => $this->person->getSignatureHtml(),

			'addable_slas'               => $addable_slas,
			'person_object_counts'       => $this->em->getRepository('DeskPRO:Person')->getPersonObjectCounts($ticket->person),
		);

        if($is_pdf) {
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

		if ($is_print) {
			$vars['print'] = true;
			return $this->render('DeskPRO:pdf_agent:view_ticket.html.twig', $vars);
		}

		return $this->render($tpl, $vars);
	}

	public function getMessagePageAction($ticket_id, $page)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_messages_blockcache = $this->_getMessageBlockInfo($ticket, $page);

		return $this->createResponse($ticket_messages_blockcache['ticket_messages_block'], 200);
	}

	protected function _getTicketPerms(Entity\Ticket $ticket)
	{
		$ticket_perms = array();
		$ticket_perms['delete'] = $this->person->PermissionsManager->TicketChecker->canDelete($ticket);
		$ticket_perms['reply'] = $this->person->PermissionsManager->TicketChecker->canReply($ticket);
		$ticket_perms['modify_set_closed'] = $this->person->PermissionsManager->TicketChecker->canSetClosed($ticket);

		foreach (array('department', 'slas', 'fields', 'assign_agent', 'assign_team', 'assign_self', 'cc', 'merge', 'labels', 'notes', 'set_hold', 'set_awaiting_agent', 'set_awaiting_user', 'set_resolved') as $p) {
			$ticket_perms["modify_$p"] = $this->person->PermissionsManager->TicketChecker->canModify($ticket, $p);
		}

		$ticket_perms['modify_messages'] = $this->person->PermissionsManager->TicketChecker->canEditMessages($ticket);

		return $ticket_perms;
	}

	public function loadTicketLogsAction($ticket_id)
	{
		$page       = $this->in->getUint('page') ?: 1;
		$filter     = $this->in->getString('filter');
		$up_to_page = $this->in->getBool('up_to_page');

		$ticket = $this->getTicketOr404($ticket_id);

		$info = $this->_getTicketLogsBlockInfo($ticket, $page, $filter == 'all' ? null :  $filter, $up_to_page);

		return $this->createResponse($info['rendered']);
	}

	public function loadAttachListAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_attachments = $this->em->getRepository('DeskPRO:TicketAttachment')->getTicketAttachments($ticket);
		$attach_to_message = array();

		foreach ($ticket_attachments as $attach) {
			if ($attach->message) {
				$attach_to_message[$attach->id] = $attach->message;
			}
		}

		$all_ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket, array());
		$counts = $this->em->getRepository('DeskPRO:TicketLog')->countTicketLogTypes($all_ticket_logs);
		$counts['attach'] = count($ticket_attachments);

		return $this->render('AgentBundle:Ticket:ticket-attach-list.html.twig', array(
			'ticket'              => $ticket,
			'filter'              => 'attach',
			'counts'              => $counts,
			'attachments'         => $ticket_attachments,
			'attach_to_message'   => $attach_to_message,
		));
	}

	protected function _getTicketLogsBlockInfo(\Application\DeskPRO\Entity\Ticket $ticket, $page = 1, $filter = null, $up_to_page = false)
	{
		if ($filter) {
			// 50 when filtered because entries are "loose"
			$per_page = 50;
		} else {
			// Only 10 when not filtered because entries are grouped,
			// so 10 is typically more like 50
			$per_page = 10;
		}

		$options = array();
		$all_ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket, $options);

		$counts = $this->em->getRepository('DeskPRO:TicketLog')->countTicketLogTypes($all_ticket_logs);
		$counts['attach'] = $this->db->fetchColumn("SELECT COUNT(*) FROM tickets_attachments WHERE ticket_id = ?", array($ticket->id));

		if ($filter) {
			$all_ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->filterTicketLogs($all_ticket_logs, $filter);
		} else {
			$all_ticket_logs = $this->em->getRepository('DeskPRO:TicketLog')->groupTicketLogs($all_ticket_logs);
		}

		$all_ticket_logs = array_chunk($all_ticket_logs, $per_page, true);

		if ($up_to_page) {
			$ticket_logs = array();
			for ($i = 0; $i < $page; $i++) {
				$p = isset($all_ticket_logs[$page-1]) ? $all_ticket_logs[$page-1] : array();
				$ticket_logs = array_merge($ticket_logs, $p);
			}
		} else {
			$ticket_logs = isset($all_ticket_logs[$page-1]) ? $all_ticket_logs[$page-1] : array();
		}

		$info = array();
		$info['ticket']      = $ticket;
		$info['num_pages']   = count($all_ticket_logs);
		$info['cur_page']    = $page;
		$info['ticket_logs'] = $ticket_logs;
		$info['filter']      = $filter;
		$info['counts']      = $counts;

		$rendered = $this->renderView('AgentBundle:Ticket:ticket-logs.html.twig', $info);
		$info['rendered'] = $rendered;

		return $info;
	}

	protected function _getMessageBlockInfo(\Application\DeskPRO\Entity\Ticket $ticket, $page, array $ticket_attachments = null, $is_pdf = false, $is_print = false)
	{
		$per_page = 25;

		if ($is_pdf || $is_print) {
			$per_page = 500;
		}

		$all_message_ids = $this->db->fetchAllCol("
			SELECT id
			FROM tickets_messages
			WHERE ticket_id = ?
			ORDER BY date_created DESC
		", array($ticket->getId()));

		$message_numbers = array();
		if ($all_message_ids) {
			$message_numbers = array_combine(array_values($all_message_ids), array_reverse(array_keys($all_message_ids)));
		}

		$message_count = count($all_message_ids);
		$num_pages = ceil($message_count / $per_page);

		$message_ids = array_slice($all_message_ids, ($page-1)*$per_page, $per_page);

		$ticket_messages = $this->em->getRepository('DeskPRO:TicketMessage')->getByIds($message_ids);

		usort($ticket_messages, function($a, $b) {
			$ts_a = $a->date_created->getTimestamp();
			$ts_b = $b->date_created->getTimestamp();

			if ($ts_a == $ts_b) return 0;
			return ($ts_a < $ts_b) ? -1 : 1;
		});

		if ($ticket_attachments === null) {
			$ticket_attachments = $this->em->getRepository('DeskPRO:TicketAttachment')->getAttachmentsForMessages($ticket_messages);
		}

		$ticket_messages_translated = $this->em->getRepository('DeskPRO:TicketMessageTranslated')->getForMessages($ticket_messages, $this->person->getLanguage()->getLocale());

		foreach ($ticket_messages as $message) {
			if (!isset($ticket_messages_translated[$message->id]) && $message->primary_translation) {
				$ticket_messages_translated[$message->id] = $message->primary_translation;
			}
		}

		// Group attachments into messages so we can place them into each message
		$ticket_message_attachments = array();
		foreach ($ticket_attachments as $attach) {
			if (!$attach['message'] || $attach['is_inline']) continue;
			if (!isset($ticket_message_attachments[$attach['message']['id']])) {
				$ticket_message_attachments[$attach['message']->getId()] = array();
			}

			$ticket_message_attachments[$attach['message']->getId()][] = $attach->getId();
		}

		$last_message_id = 0;

		$ticket_messages_num = array();
		foreach ($ticket_messages as $m) {

			$ticket_messages_num[$m['id']] = $message_numbers[$m['id']] + 1;

			if ($m['id'] > $last_message_id) {
				$last_message_id = $m['id'];
			}
		}

		$ticket_messages_block = '';

		$all_feedback = $this->em->getRepository('DeskPRO:TicketFeedback')->getFeedbackForTicket($ticket);

		// Ticket logs to do with forwarded messages
		$fwd_logs = $this->em->createQuery("
			SELECT log, person
			FROM DeskPRO:TicketLog log
			LEFT JOIN log.person person
			WHERE log.ticket = ?0 AND log.action_type = 'message_forwarded'
		")->execute(array($ticket));

		$ticket_fwd_logs = array();
		foreach ($fwd_logs as $log) {
			$mid = $log->details['message_id'];
			if (!isset($ticket_fwd_logs[$mid])) {
				$ticket_fwd_logs[$mid] = array();
			}

			$ticket_fwd_logs[$mid][] = $log;
		}

		if ($ticket_messages) {
            if($is_pdf) {
                $tpl = 'DeskPRO:pdf_agent:ticket-messages-batch.html.twig';
            }
            else {
                $tpl = 'AgentBundle:Ticket:ticket-messages-batch.html.twig';
            }
			$ticket_messages_block = $this->renderView($tpl, array(
				'ticket'                     => $ticket,
				'ticket_messages'            => $ticket_messages,
				'ticket_messages_translated' => $ticket_messages_translated,
				'ticket_messages_num'        => $ticket_messages_num,
				'ticket_message_attachments' => $ticket_message_attachments,
				'ticket_fwd_logs'            => $ticket_fwd_logs,
				'ticket_attachments'         => $ticket_attachments,
				'all_feedback'               => $all_feedback,
				'message_page'               => $page,
				'message_count'              => $message_count,
				'message_page_count'         => $num_pages
			));
		}

		$ticket_messages_blockcache = array(
			'status'                     => $ticket->getStatusCode(),
			'urgency'                    => $ticket->urgency,
			'department_id'              => $ticket->getDepartmentId(),
			'category_id'                => $ticket->getCategoryId(),
			'product_id'                 => $ticket->getProductId(),
			'workflow_id'                => $ticket->getWorkflowId(),
			'priority_id'                => $ticket->getPriorityId(),
			'is_hold'                    => $ticket->is_hold,
			'agent_id'                   => $ticket->getAgentId(),
			'agent_team_id'              => $ticket->getAgentTeamId(),
			'is_locked'                  => $ticket->hasLock(),
			'locked_by_agent_id'         => $ticket->hasLock() ? $ticket->locked_by_agent->getId() : null,
			'locked_by_agent_name'       => $ticket->hasLock() ? $ticket->locked_by_agent->getDisplayName() : null,

			'ticket_messages_block'      => $ticket_messages_block,
			'ticket_messages'            => $ticket_messages,
			'ticket_messages_translated' => $ticket_messages_translated,
			'ticket_messages_num'        => $ticket_messages_num,
			'ticket_attachments'         => $ticket_attachments,
			'ticket_message_attachments' => $ticket_message_attachments,
			'message_count'              => $message_count,
			'message_page'               => $page,
			'message_page_count'         => $num_pages,
			'last_message_id'            => $last_message_id,
		);

		return $ticket_messages_blockcache;
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

		return $this->createJsonResponse(array(
			'success' => 1,
			'can_view' => $this->person->PermissionsManager->TicketChecker->canView($ticket)
		));
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
			$person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));
		} elseif ($email_address = $this->in->getString('email_address')) {

			if (!\Orb\Validator\StringEmail::isValueValid($email_address)) {
				return $this->createJsonResponse(array(
					'error' => true,
					'error_code' => 'invalid_email'
				));
			} elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($email_address)) {
				return $this->createJsonResponse(array(
					'error' => true,
					'error_code' => 'invalid_email_gatewayaccount'
				));
			}

			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_address);

			if (!$person) {
				$person = new Person();
				$person->setEmail($email_address);
			}
		}

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($person->is_agent) {
			return $this->createJsonResponse(array(
				'error'      => true,
				'error_code' => 'is_agent',
				'cc_list'    => $this->_getTicketCcList($ticket)
			));
		}

		if ($person->id) {
			if ($ticket->hasParticipantPerson($person) || $ticket->person->getId() == $person->getId()) {
				return $this->createJsonResponse(array(
					'error'      => true,
					'error_code' => 'is_dupe',
				));
			}
		}

		$this->db->beginTransaction();

		try {

			if (!$person->id) {
				$this->em->persist($person);
				$this->em->flush();
			}

			$part = $ticket->addParticipantPerson($person);
			if ($part) {
				$this->em->persist($part);
			}
			$this->em->persist($ticket);
			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'cc_list' => $this->_getTicketCcList($ticket)
		));
	}

	############################################################################
	# remove-participant
	############################################################################

	protected function _getTicketCcList($ticket)
	{
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

		$cc_list = $this->renderView('AgentBundle:Ticket:view-user-cc-list.html.twig', array(
			'user_parts' => $user_parts,
			'ticket_perms' => $this->_getTicketPerms($ticket),
		));

		return $cc_list;
	}

	public function removeParticipantAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_cc');
		$person = $this->em->find('DeskPRO:Person', $this->in->getUint('person_id'));

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$part = $this->em->createQuery("
			SELECT part
			FROM DeskPRO:TicketParticipant part
			WHERE part.ticket = ?0 AND part.person = ?1
		")->setParameters(array($ticket, $person))->setMaxResults(1)->getOneOrNullResult();

		if (!$part) {
			return $this->createJsonResponse(array('success' => false));
		}

		$this->db->beginTransaction();

		try {
			$this->em->remove($part);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
            throw $e;
		}

		return $this->createJsonResponse(array('success' => true, 'cc_list' => $this->_getTicketCcList($ticket)));
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

		return $this->createJsonResponse(array('sucess' => true, 'cc_list' => $this->_getTicketCcList($ticket)));
	}


	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_labels');

		$tm = $this->container->getTicketManager();
		$tm->markAsManaged($ticket);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');
		$ticket->getLabelManager()->setLabelsArray($labels);
		$this->em->persist($ticket);

		$context = $tm->createAgentExecutorContext($this->person, 'update', 'web');
		$tm->saveTicket($ticket, $context);

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# ajax-save-reply
	############################################################################

	public function ajaxSaveReplyAction($ticket_id)
	{
		if ($this->in->getBool('reply_is_trans')) {
			$request_message_orig  = $this->in->getHtml('message_original');
			$request_message_trans = $this->in->getHtml('message');
		} else {
			$request_message_orig  = $this->in->getHtml('message');
			$request_message_trans = '';
		}

		if (!$request_message_orig || $request_message_orig == trim($this->person->getPref('agent.ticket_signature'))) {
			return $this->createJsonResponse(array('error' => 'no_message'));
		}

		if ($this->in->getBool('options.is_note')) {
			$ticket = $this->getTicketOr404($ticket_id, 'modify_notes');
		} else {
			$ticket = $this->getTicketOr404($ticket_id, 'reply');
		}

		$ticket_context = $this->container->getTicketManager()->createAgentExecutorContext($this->person, 'newreply', 'web');
		$this->container->getTicketManager()->markAsManaged($ticket);

		$action_type = $this->in->getString('options.action');
		$macro_id = Strings::extractRegexMatch('#macro:(\d+)#', $action_type, 1);
		if ($macro_id) {
			$action_type = 'macro';
		} else {
			if (!$action_type) {
				$action_type = 'awaiting_user';
			}
		}

		$macro = null;
		if ($macro_id) {
			$macro = $this->em->find('DeskPRO:TicketMacro', $macro_id);
		}

		$refresh_tab = false;

		$factory = new ActionsFactory();
		$collection = new ActionsCollection();

		$set_status = null;
		if ($macro) {
			foreach ($macro->actions as $action) {
				$action = $factory->createFromInfo($action);
				if ($action) {

					if ($action instanceof AgentAction || $action instanceof AgentTeamAction || $action instanceof ReplyAction || $action instanceof ReplySnippetAction) {
						// Ignore, the replybox itself changed for these actions
					} else {
						$refresh_tab = true;

						if ($action instanceof StatusAction) {
							$set_status = $action->getFullStatus();
						}

						$collection->add($action);
					}
				}
			}
		} else {
			$set_status = $action_type;
		}

		if ($set_status) {
			switch ($set_status) {
				case 'resolved':       if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'set_resolved')) $set_status = $ticket['status']; break;
				case 'awaiting_agent': if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_agent')) $set_status = $ticket['status']; break;
				case 'awaiting_user':  if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'set_awaiting_user')) $set_status = $ticket['status']; break;
			}
		}

		#------------------------------
		# Handle new message
		#------------------------------

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['ip_address'] = dp_get_user_ip_address();
		$message['creation_system'] = Entity\TicketMessage::CREATED_WEB_AGENT_PORTAL;

		if ($this->in->getBool('is_html_reply')) {
			$message_text = $request_message_orig;

			$message_test = $message_text;
			$message_test = Strings::trimHtml($message_test);
			if (!$message_test || Strings::compareHtml($message_test, $this->person->getSignatureHtml())) {
				return $this->createJsonResponse(array('error' => 'no_message'));
			}

			$message_text = Strings::prepareWysiwygHtml($message_text);
			$message->message = $message_text;

			$notify_agent_ids = array();
			preg_match_all('/<span[^>]+data-notify-agent-id="(\d+)"/i', $this->in->getRaw('message'), $matches, PREG_SET_ORDER);
			foreach ($matches AS $match) {
				$notify_agent_ids[] = $match[1];
			}
		} else {
			$message->setMessageText($request_message_orig);
			$notify_agent_ids = array();
		}

		if ($this->in->getBool('options.is_note')) {
			$message['is_agent_note'] = true;
		}

		if ($notify_agent_ids && $message['is_agent_note']) {
			$agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
			$agent_chat->disableOfflineEmailAlert(); // we'll handle offline notifs as part of normal notifications

			$notify_chat   = array();
			$notify_email  = array();

			$notify_agent_ids = array_unique($notify_agent_ids);
			foreach ($notify_agent_ids as $agent_id) {
				if (!($agent = $this->container->getAgentData()->get($agent_id))) {
					continue;
				}

				$notify_chat[$agent->id] = $agent;

				$pref = $agent->getPref('agent_notif.ticket_mention', 'always_send');
				if ($pref == 'always_send' || ($pref == 'smart_send' && !$this->container->getAgentData()->isAgentOnline($agent))) {
					$notify_email[$agent->id] = $agent;
				}
			}

			if ($notify_chat) {
				$notify_text = $this->person->getDisplayName() . " alerted you in a note in {{t-$ticket->id}}: $ticket->subject";
				$agent_chat->sendAgentMessage($notify_text, array_keys($notify_chat));
			}

			if ($notify_email) {
				$ticket_context->getVars()->set('mention_agents', $notify_email);
			}
		}

		foreach ($this->in->getCleanValueArray('attach') as $blob_id) {
			$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
			if ($blob) {
				$attach = new Entity\TicketAttachment();
				$attach['blob'] = $blob;
				$attach['person'] = $this->person;

				$message->addAttachment($attach);
			}
		}

		foreach ($this->in->getCleanValueArray('blob_inline_ids', 'uint', 'discard') as $blob_id) {
			$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
			if ($blob) {
				$attach = new Entity\TicketAttachment();
				$attach['blob'] = $blob;
				$attach['person'] = $this->person;
				$attach->is_inline = true;

				$message->addAttachment($attach);
			}
		}

		$message->convertEmbeddedImagesToInlineAttach();
                
                if ($this->in->getBool('options.is_snippet')) {
                    $snippet = $this->em->find('DeskPRO:TextSnippet', (int) $this->in->getString('options.snippet_id'));
                    
                    if ($snippet) {
                        $snippetLog = new Entity\TextSnippetLog();
                    
                        $snippetLog['ticket']   = $ticket;
                        $snippetLog['person']   = $this->getPerson();
                        $snippetLog['snippet']  = $snippet;
                        
                        $this->em->persist($snippetLog);
                        $this->em->flush();
                    }
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
				$ticket_context->getVars()->set('mute_user_emails', true);
			}
		}

		// havent persisted the messag yet, it was just for dupe checking
		if (App::getSetting('core_tickets.enable_billing') && $this->in->getUint('charge_time')) {
			$charge = $ticket->addCharge($this->person, $this->in->getUint('charge_time'));
		} else {
			$charge = false;
		}

		// Translated version
		if ($this->in->getString('reply_is_trans') && $request_message_trans) {
			$message_translated = new Entity\TicketMessageTranslated();
			$message_translated->setTicketMessage($message);
			$message_translated->message = $request_message_trans;
			$message_translated->from_lang_code = $this->person->getLanguage()->getLocale();
			$message_translated->lang_code = $this->in->getString('reply_is_trans');
			$this->em->persist($message_translated);

			$message->primary_translation = $message_translated;
		}
                
		#------------------------------
		# Handle CC'ing/parts
		#------------------------------

		$add_parts = array();
		$new_user_ids = array();
		$rem_parts = array();
		$changed_parts = false;

		$email_validator = new \Orb\Validator\StringEmail();

		$del_cc_emails = $this->container->getIn()->getCleanValueArray('delcc', 'string', 'discard');
		$del_cc_emails = array_map('strtolower', $del_cc_emails);

		$add_cc_emails = $this->container->getIn()->getCleanValueArray('addcc', 'string', 'discard');
		$add_cc_emails = array_map('strtolower', $add_cc_emails);

		$add_cc_emails = array_filter($add_cc_emails, function ($v) use ($del_cc_emails) {
			return !in_array($v, $del_cc_emails);
		});

		if ($add_cc_emails) {
			foreach ($add_cc_emails as $email) {
				if (!$email || !$email_validator->isValid($email) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($email)) {
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
		}

		if ($del_cc_emails) {
			foreach ($del_cc_emails as $email) {
				if (!$email || !$email_validator->isValid($email)) {
					continue;
				}

				$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);

				if ($person) {
					$changed_parts = true;
					$rem_parts[] = $person;
				}
			}
		}

		if ((!$message['is_agent_note'] || $macro) && $collection->countActions()) {
			$collection->apply($ticket->getTicketLogger(), $ticket, $this->person);
		}

		#------------------------------
		# Save
		#------------------------------

		$this->db->beginTransaction();

		$changed_agent = false;
		$changed_team  = false;
		try {

			if ($add_parts) {
				foreach ($add_parts as $p) {
					$ticket->addParticipantPerson($p);
				}
			}
			if ($rem_parts) {
				foreach ($rem_parts as $p) {
					$ticket->removeParticipantPerson($p);
				}
			}

			#------------------------------
			# Handle actions
			#------------------------------

			if ($this->in->getInt('options.agent_id') != -1 && $this->in->getBool('options.do_assign_agent')) {
				$changed_agent = true;
				$ticket['agent_id'] = $this->in->getUint('options.agent_id');
			}
			if ($this->in->getInt('options.agent_team_id') != -1 && $this->in->getBool('options.do_assign_team')) {
				$changed_team = true;
				$ticket['agent_team_id'] = $this->in->getUint('options.agent_team_id');
			}

			if (!$message['is_agent_note'] || $macro) {
				if ($action_type != 'macro') {
					$ticket['status'] = $action_type;
				}

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

			$this->container->getTicketManager()->saveTicket($ticket, $ticket_context);

			$this->em->getRepository('DeskPRO:Draft')->deleteDraft('ticket', $ticket->id);
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		if (!$message['is_agent_note'] || $macro) {
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
		if ($this->in->getUint('client_messages_since') > 0) {
			$client_messages = $this->em->getRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		$close_tab = $this->in->getBool('options.close_tab');

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$this->in->getUint('message_page')
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
			'agent_signature' => $this->person->getSignature(),
	        'agent_signature_html' => $this->person->getSignatureHtml(),
			'ticket_perms' => $this->_getTicketPerms($ticket),
		));

		$cc_list = $this->renderView('AgentBundle:Ticket:view-user-cc-list.html.twig', array(
			'user_parts' => $user_parts,
			'ticket_perms' => $this->_getTicketPerms($ticket),
		));

		if ($charge) {
			$charge_html = $this->renderView('AgentBundle:Ticket:view-billing-row.html.twig', array(
				'ticket' => $ticket,
				'charge' => $charge
			));
		} else {
			$charge_html = false;
		}

		$drafts = $this->em->getRepository('DeskPRO:Draft')->getActiveDrafts('ticket', $ticket->id);
		$data['active_drafts'] = $this->_renderActiveDrafts($ticket, $drafts);

		$error_messages = array();
		if ($set_status == 'resolved') {
			$newticket = new NewTicket($this->em, $this->person);
			$newticket->setValuesFromTicket($ticket);
			$validator = new NewTicketValidator();

			$layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($ticket->department->id);
			$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::EDIT_TICKET, $ticket);
			$validator->setLayout($layout);
			if (!$validator->isValid($newticket)) {
				foreach ($validator->getErrorsInfo() as $info) {
					$error_messages[] = $info['message'];
				}

				// Need to undo setting status!
				$close_tab = false;
				$ticket->status = 'awaiting_agent';
				$this->container->getTicketManager()->saveTicket($ticket, $ticket_context);
			}
		}

		$data = array_merge($data, array(
			'via_reply'                        => true,
			'updated_agent_parts_html'         => isset($updated_agent_parts) ? $updated_agent_parts : '',
			'updated_agent_parts_html_count'   => isset($updated_agent_parts_count) ? $updated_agent_parts_count : null,
			'replybox_html'                    => $replybox,
			'charge_html'                      => $charge_html,
			'changed_agent'                    => $changed_agent,
			'agent_id'                         => $ticket['agent_id'],
			'changed_team'                     => $changed_team,
			'agent_team_id'                    => $ticket['agent_team_id'],
			'status'                           => $ticket['status'],
			'close_tab'                        => $close_tab,
			'refresh_tab'                      => $refresh_tab,
			'client_messages'                  => $client_messages,
			'cc_list'                          => $cc_list,
			'error_messages'                   => $error_messages ?: false,
			'notified_agents'                  => $notify_agent_ids,
			'can_view'                         => $this->person->PermissionsManager->TicketChecker->canView($ticket),
			'api_data'                         => $ticket->toApiData()
		));

		return $this->createJsonResponse($data);
	}

	public function updateViewsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$data = $this->_getMessageBlockInfo(
			$ticket,
			$this->in->getUint('message_page')
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
			'agent_signature' => $this->person->getSignature(),
	        'agent_signature_html' => $this->person->getSignatureHtml(),
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
			'api_data' => $ticket->toApiData()
		));

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-get-message-text
	############################################################################

	public function ajaxGetMessageTextAction($message_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		return $this->createJsonResponse(array(
			'message_id' => $message->getId(),
			'message_text' => $message->getMessageText(),
			'message_html' => $message->getMessageHtml(),
		));
	}

	public function ajaxSaveMessageTextAction($message_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		$old_message = $message->message;
		$old_full_message = $message->message_full;

		$new_message = $this->in->getHtmlCore('message_html');
		$new_message = Strings::trimHtml($new_message);
		$new_message = Strings::prepareWysiwygHtml($new_message);
		$message->setMessageHtml($new_message);

		$ticket_log = new TicketLog();
		$ticket_log->ticket      = $ticket;
		$ticket_log->person      = $this->person;
		$ticket_log->action_type = 'message_edit';
		$ticket_log->id_object   = $message->getId();
		$ticket_log->details     = array(
			'message_id'       => $message->getId(),
			'old_message'      => $old_message,
			'old_full_message' => $old_full_message
		);

		$this->db->beginTransaction();
		try {
			$this->em->persist($message);
			$this->em->persist($ticket_log);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'message_id' => $message->getId(),
			'message_text' => $message->getMessageText(),
			'message_html' => $message->getMessageHtml()
		));
	}

	public function ajaxSetNoteAction($message_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		$message->is_agent_note = $this->in->getBool('is_note');

		$this->em->persist($message);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'message_id' => $message->getId(),
			'ticket_id'  => $ticket->getId(),
			'is_note'    => $message->is_agent_note
		));
	}

	public function deleteMessageAction($message_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		if (count($ticket->messages) == 1) {
			$this->db->replace('tickets_deleted', array(
				'ticket_id' => $ticket->id,
				'by_person_id' => $this->person->id,
				'new_ticket_id' => 0,
				'reason' => $this->in->getString('reason'),
				'date_created' => date('Y-m-d H:i:s')
			));

			$ticket->setStatus('hidden.deleted');
			$this->em->persist($ticket);

			$hidden_data = $this->_getHiddenBarData($ticket);

			return $this->createJsonResponse(array(
				'success' => true,
				'ticket_deleted' => true,
				'hidden_html' => $this->renderView('AgentBundle:Ticket:view-hidden-bar.html.twig', array(
					'ticket' => $ticket,
					'ticket_perms' => $this->_getTicketPerms($ticket),
					'ticket_deleted' => $hidden_data['ticket_deleted'],
					'hard_delete_time' => $hidden_data['hard_delete_time'],
				))
			));
		} else {
			$m = $message;
			$log_data = array();
			$log_data['message_id']       = $m->id;
			$log_data['person_id']        = $m->person->id;
			$log_data['person_name']      = $m->person->display_name;
			$log_data['is_agent_note']    = $m->is_agent_note;
			$log_data['is_agent_message'] = $m->person->is_agent;
			$log_data['old_message']      = $m->getMessageHtml();

			$log = new TicketLog();
			$log->ticket      = $ticket;
			$log->person      = $this->person;
			$log->action_type = 'message_removed';
			$log->id_before   = $m->id;
			$log->details = $log_data;

			$this->em->persist($log);
			$this->em->remove($message);
			$this->em->flush();

			return $this->createJsonResponse(array(
				'success' => true
			));
		}
	}

	public function getMessageAttachmentsAction($message_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->render('AgentBundle:Ticket:message-attachments-overlay.html.twig', array(
			'ticket' => $ticket,
			'message' => $message,
			'attachments' => $message->attachments
		));
	}

	public function deleteMessageAttachmentAction($message_id, $attachment_id)
	{
		/** @var $message \Application\DeskPRO\Entity\TicketMessage */
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		$ticket = null;
		if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
			$ticket = $message->ticket;
		}

		if (!$ticket) {
			throw $this->createNotFoundException();
		}

		$this->container->getTicketManager()->markAsManaged($ticket);

		$attachment = false;
		foreach ($message->attachments AS $test_attachment) {
			if ($test_attachment->id == $attachment_id) {
				$attachment = $test_attachment;
				break;
			}
		}

		if (!$attachment) {
			throw $this->createNotFoundException();
		}

		if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$ticket_log = new TicketLog();
		$ticket_log->ticket      = $ticket;
		$ticket_log->person      = $this->person;
		$ticket_log->action_type = 'attach_removed';
		$ticket_log->id_object   = $message->getId();
		$ticket_log->id_before   = $attachment->id;

		$blob = $attachment->blob;
		$log_data['attach_id']       = $attachment->id;
		$log_data['blob_id']         = $blob->id;
		$log_data['filename']        = $blob->filename;
		$log_data['filesize']        = $blob->filesize;
		$log_data['content_type']    = $blob->content_type;
		$ticket_log->details = $log_data;

		$this->em->persist($ticket_log);
		$this->em->remove($attachment);

		$embed_code = str_replace(':image:', ':[^:]*:', preg_quote($attachment->blob->getEmbedCode(true, 'image'), '/'));
		$message->message = preg_replace("/$embed_code/i", '', $message->message);
		$this->em->persist($message);

		// need this to be removed, but don't want to trigger a change log for it as we're inserting it manually
		$message->attachments->removeElement($attachment);

		$this->container->getBlobStorage()->deleteBlobRecord($attachment->blob);
		$this->db->delete('tickets_attachments', array('id' => $attachment->id));

		$ticket_attachments = array();
		$ticket_message_attachments = array();
		foreach ($message->attachments AS $message_attach) {
			$ticket_attachments[$message_attach->id] = $message_attach;
			$ticket_message_attachments[$message->id][] = $message_attach->id;
		}

		$this->em->flush();

		// Delete the blob itself
		$this->container->getBlobStorage()->deleteBlobRecord($blob);

		return $this->createJsonResponse(array(
			'success' => true,
			'message_html' => $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $message,
				'ticket_message_attachments' => $ticket_message_attachments,
				'ticket_attachments' => $ticket_attachments,
				'ticket' => $ticket
			))
		));
	}

	############################################################################
	# ajax-save-actions
	############################################################################

	public function ajaxSaveActionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify');

		$tm = $this->container->getTicketManager();
		$tm->markAsManaged($ticket);

		$context = $tm->createAgentExecutorContext($this->person, 'update', 'web');

		$old_department_id = $ticket->getDepartmentId();
		$new_department_id = $ticket->getDepartmentId();

		$language = $ticket->language;

		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$error_messages = array();

		$perms_before = $this->_getTicketPerms($ticket);

		$was_hidden = $ticket->status == 'hidden';

		$macro_id = $this->in->getUint('macro_id');
		if ($macro_id) {
			$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);
			if ($macro) {
				$macro->performOnTicket($ticket, $this->person);
				$tm->saveTicket($ticket, $context);
			}
		} else {
			$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
			$ticket_edit->setPersonContext($this->person);

			// Validate based on department...
			$newticket = new \Application\AgentBundle\Form\Model\NewTicket($this->em, $this->person);
			$newticket->setValuesFromTicket($ticket);

			foreach (array('category_id', 'priority_id', 'product_id', 'workflow_id') as $f) {
				if ($this->in->checkIsset("actions.$f")) {
					$newticket->{$f} = $this->in->getUint("actions.$f");
				}
			}
			if (isset($_REQUEST['custom_fields'])) {
				$newticket->ticket_fields = $_REQUEST['custom_fields'];
			}

			if ($this->in->getString('actions.status') == 'resolved') {
				$newticket->status = 'resolved';
			} else {
				$newticket->status = '';
			}

			$validator = new NewTicketValidator();
			$layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($newticket->department_id);
			$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::EDIT_TICKET, $newticket->getMockTicket());
			$validator->setLayout($layout);

			$actions = $this->in->getCleanValueArray('actions', 'raw', 'raw');

			if (count($actions) == 1 && isset($actions['department_id'])) {
				// Validation not on dep changes,
				// because changing dep could change validation options
				$new_department_id = $actions['department_id'];
			} else if ($ticket->status == 'hidden' && count($actions) == 2 && isset($actions['status']) && isset($actions['hidden_status'])) {
				// skip validation just restoring a deleted ticket, validation will apply after
			} else {
				if (!$validator->isValid($newticket)) {
					$free = array();
					foreach ($validator->getErrorsInfo() as $info) {
						$free[] = htmlspecialchars($info['message']);
					}

					return $this->createJsonResponse(array('error' => true, 'error_messages' => $free));
				}
			}

			$result = $ticket_edit->applyActions($actions);

			// If department is changed,
			// then we re-output the holder template
			$is_dep_changed = false;
			$event_listener = new PropertyChangedCallback(function ($sender, $propertyName, $oldValue, $newValue) use (&$is_dep_changed) {
				if ($propertyName == 'department') {
					$is_dep_changed = true;
				}
			});
			$ticket->addPropertyChangedListener($event_listener);

			if ($this->in->getBool('with_set_agent_parts')) {
				$set_parts = $this->in->getCleanValueArray('set_agent_part_ids', 'uint', 'discard');
				$agents = $this->em->getRepository('DeskPRO:Person')->getPeopleFromIds($set_parts);
				$ticket->setAgentParticipants($agents);
			}

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

			$tm->saveTicket($ticket, $context);
		}

		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		$data = array('data' => array());
		if (isset($result['new_reply'])) {
			$data['data']['new_reply'] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $result['new_reply']
			));
		}

		// need to reload the whole ticket if we flipped the language type
		$was_rtl = ($language && $language->is_rtl);
		$is_rtl = ($ticket->language && $ticket->language->is_rtl);
		$data['data']['reload'] = (($was_rtl && !$is_rtl) || (!$was_rtl && $is_rtl));

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$data['holders'] = $this->renderView('AgentBundle:Ticket:view-page-display-holders.html.twig', array(
			'ticket'              => $ticket,
			'ticket_options'      => $ticket_options,
			'custom_fields'       => $custom_fields,
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

		$data['data']['can_view'] = $this->person->PermissionsManager->TicketChecker->canView($ticket);

		$perms_after = $this->_getTicketPerms($ticket);

		if ($perms_before['reply'] != $perms_after['reply']) {
			$data['data']['refresh'] = true;
		}

		if (isset($ticket_edit) && $ticket_edit->getPermErrors()) {
			$data['data']['perm_errors'] = $ticket_edit->getPermErrors();
			$data['data']['refresh'] = true;
		}

		if ($was_hidden && $ticket->status != 'hidden') {
			$data['data']['refresh'] = true;
		}

		// If the department changed and we have new field options,
		// then we'll need to refresh the ticket so those new validation options
		// are enforced
		if (!isset($data['data']['refresh']) && $old_department_id != $ticket->getDepartmentId()) {
			$old_page_ids = array();
			$new_page_ids = array();

			$old_page = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($old_department_id);
			$new_page = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($new_department_id);

			// - We only care about fields that have validation
			// - The actual field show/hide changes are handled in JS on the client
			// - So only when the current validation scheme changes do
			// we need to resort to re-loading the ticket tab
			$fn_check_has_validator = function($x) use ($field_manager) {
				switch ($x->getFieldType()) {
					case 'product':
						return App::getSetting('core_tickets.field_validation_ticket_prod_agent_required');
						break;

					case 'category':
						return App::getSetting('core_tickets.field_validation_ticket_cat_agent_required');
						break;

					case 'priority':
						return App::getSetting('core_tickets.field_validation_ticket_pri_agent_required');
						break;

					case 'workflow':
						return App::getSetting('core_tickets.field_validation_ticket_work_agent_required');
						break;

					case 'ticket_field':
						$field = $field_manager->getFieldFromId($x->getFieldId());
						if (!$field) return false;
						return $field->getOption('agent_required');
						break;
					case 'user_field':
						$field = $field_manager->getFieldFromId($x->getFieldId());
						if (!$field) return false;
						return $field->getOption('agent_required');
						break;
				}

				return false;
			};

			foreach ($old_page as $x) {
				if ($fn_check_has_validator($x)) {
					$old_page_ids[$x->getId()] = $x->getId();
				}
			}
			foreach ($new_page as $x) {
				if ($fn_check_has_validator($x)) {
					$new_page_ids[$x->getId()] = $x->getId();
				}
			}

			if (count($old_page_ids) != count($new_page_ids) || array_diff($old_page_ids, $new_page_ids) || array_diff($new_page_ids, $old_page_ids)) {
				$data['data']['refresh'] = true;
			}
		}

		$data['data']['api_data'] = $ticket->toApiData();

		return $this->createJsonResponse($data);
	}

	public function ajaxSaveSubjectAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_fields');

		$subject = $this->in->getString('subject');

		if (!$subject) {
			$subject = App::getTranslator()->getPhraseText('user.tickets.no_subject');
		}

		$ticket->subject = $subject;

		$this->db->beginTransaction();
		try {
			$this->em->persist($ticket);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function ajaxChangeUserEmailAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_fields');

		$email_id = $this->in->getUint('email_id');

		$new_email = $ticket->person->getEmailId($email_id);
		if ($new_email) {
			$ticket->person_email = $new_email;
			$this->em->persist($ticket);
			$this->em->flush();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# ajax-get-macro-actions
	############################################################################

	public function ajaxGetMacroAction($ticket_id)
	{
		if (!$ticket_id) {
			$ticket = new \Application\DeskPRO\Entity\Ticket();
		} else {
			$ticket = $this->getTicketOr404($ticket_id);
		}

		$GLOBALS['DP_ACTIVE_TICKET'] = $ticket;

		$macro_id = $this->in->getUint('macro_id');

		/** @var $macro \Application\DeskPRO\Entity\TicketMacro */
		$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);

		if (!$macro || (!$macro->is_global && $macro->person && $macro->person->getId() != $this->person->getId())) {
			throw $this->createNotFoundException();
		}

		$descriptions = $macro->getActionDescriptions($ticket);

		return $this->createJsonResponse(array(
			'macro_id' => $macro->id,
			'descriptions' => $descriptions,
		));
	}

	public function applyMacroAction($ticket_id, $macro_id)
	{
		/** @var $ticket \Application\DeskPRO\Entity\Ticket */
		$ticket = $this->getTicketOr404($ticket_id, 'edit');

		/** @var $macro \Application\DeskPRO\Entity\TicketMacro */
		$macro = $this->em->getRepository('DeskPRO:TicketMacro')->find($macro_id);

		if (!$macro || (!$macro->is_global && $macro->person && $macro->person->getId() != $this->person->getId())) {
			throw $this->createNotFoundException();
		}

		$actions_collection = $macro->getActionsCollection($ticket);

		$permission_errors = false;
		$this->db->beginTransaction();
		try {
			if (!$actions_collection->applyCheckPermission($ticket, $this->person)) {
				$permission_errors = true;
			} else {
				$actions_collection->apply($ticket->getTicketLogger(), $ticket, $this->person);
				$this->em->persist($ticket);
				$this->em->flush();
				$ticket->getTicketLogger()->done();
				$this->db->commit();
			}
		} catch (\Exception $e) {
			$this->db->rollback();
		}

		if ($permission_errors) {
			return $this->createJsonResponse(array(
				'ticket_id' => $ticket->getId(),
				'macro_id' => $macro->getId(),
				'success' => false,
				'error' => 'permissions'
			));
		}

		return $this->createJsonResponse(array(
			'ticket_id' => $ticket->getId(),
			'macro_id' => $macro->getId(),
			'close_tab' => (isset($GLOBALS['DP_TICKET_CLOSE_TAB']) && $GLOBALS['DP_TICKET_CLOSE_TAB']),
			'success' => true,
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
	# get-full-message
	############################################################################

	public function ajaxGetFullMessageAction($message_id)
	{
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		if (!$message) {
			throw $this->createNotFoundException();
		}

		$ticket = $this->getTicketOr404($message->ticket->getId());

		$data = array(
			'ticket_id'    => $ticket->getId(),
			'message_id'   => $message->getId(),
			'message_full' => $message->getMessageFull()
		);

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
	# add-charge
	############################################################################

	public function addChargeAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if ($this->in->getString('billing_type') == 'amount') {
			$amount = $this->in->getFloat('amount');
			$time = null;
		} else {
			$amount = null;
			$time = (
				3600 * $this->in->getUint('hours')
				+ 60 * $this->in->getUint('minutes')
				+ $this->in->getUint('seconds')
			);
		}

		$comment = $this->in->getString('billing_comment');

		$charge = $ticket->addCharge($this->person, $time, $amount, $comment);
		if ($charge) {
			$this->em->persist($ticket);
			$this->em->flush();

			return $this->createJsonResponse(array(
				'inserted' => true,
				'html' => $this->renderView('AgentBundle:Ticket:view-billing-row.html.twig', array(
					'ticket' => $ticket,
					'charge' => $charge
				))
			));
		} else {
			return $this->createJsonResponse(array('inserted' => false));
		}
	}
	
	############################################################################
	# edit-charge
	############################################################################

	public function editChargeAction($ticket_id, $charge_id)
	{
		if (!$this->person->hasPerm('agent_tickets.modify_billing')) {
			//throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}
		
		$ticket = $this->getTicketOr404($ticket_id);
		
		$charge = $this->em->createQuery('
			SELECT c
			FROM DeskPRO:TicketCharge c
			WHERE c.ticket = ?0 AND c.id = ?1
		')->setParameters(array($ticket, $charge_id))->getOneOrNullResult();

		if (!$charge) {
			return $this->createJsonResponse(array(
				'success' => false
			));
		}
		
		$old_amount	= $charge->amount;
		$old_time	= $charge->charge_time;
		$old_comment	= $charge->comment;
		
		$amount = $this->in->getFloat('amount');
		
		$time = (
			3600 * $this->in->getUint('hours')
			+ 60 * $this->in->getUint('minutes')
			+ $this->in->getUint('seconds')
		);

		$comment = $this->in->getString('billing_comment');
		
		$charge->charge_time	= $time;
		$charge->amount		= $amount;
		$charge->comment	= $comment;
		
		$ticket_log = new TicketLog();
		$ticket_log->ticket      = $ticket;
		$ticket_log->person      = $this->person;
		$ticket_log->action_type = 'modify_billing';
		$ticket_log->id_object   = $charge->id;
		$ticket_log->details     = array(
			'charge_id'	=> $charge->id,
			'old_amount'	=> $old_amount,
			'old_time'	=> $old_time,
			'new_amount'	=> $charge->amount,
			'new_time'	=> $charge->charge_time,
			'old_comment'	=> $old_comment,
			'new_comment'	=> $charge->comment
		);
		
		$this->em->persist($charge);
		$this->em->persist($ticket_log);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'updated'	=> true,
			'html' => $this->renderView('AgentBundle:Ticket:view-billing-row.html.twig', array(
				'ticket' => $ticket,
				'charge' => $charge
			))
		));
		
	}

	public function deleteChargeAction($ticket_id, $charge_id, $security_token)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$this->ensureAuthToken('delete_charge', $security_token);

		$charge = $this->em->createQuery('
			SELECT c
			FROM DeskPRO:TicketCharge c
			WHERE c.ticket = ?0 AND c.id = ?1
		')->setParameters(array($ticket, $charge_id))->getOneOrNullResult();

		if (!$charge) {
			return $this->createJsonResponse(array(
				'success' => false
			));
		}

		$this->em->remove($charge);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	############################################################################
	# add-sla
	############################################################################

	public function addSlaAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_slas');
		$tm = $this->container->getTicketManager();
		$tm->markAsManaged($ticket);

		$sla = $this->em->getRepository('DeskPRO:Sla')->find($this->in->getUint('sla_id'));
		if (!$sla || $sla->apply_type != 'manual') {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$ticket_sla = $ticket->addSla($sla);
		if ($ticket_sla && !$ticket_sla->id) {
			$this->em->persist($ticket_sla);
			$this->em->flush();

			$context = $tm->createAgentExecutorContext($this->person, 'update', 'web');
			$tm->saveTicket($ticket, $context);

			$data = array(
				'inserted' => true,
				'html' => $this->renderView('AgentBundle:Ticket:view-sla-row.html.twig', array(
					'ticket' => $ticket,
					'ticket_sla' => $ticket_sla,
					'ticket_perms' => $this->_getTicketPerms($ticket)
				))
			);
		} else {
			$data = array('inserted' => false);
		}

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

	public function deleteSlaAction($ticket_id, $sla_id, $security_token)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_slas');

		$sla = $this->em->getRepository('DeskPRO:Sla')->find($sla_id);
		if (!$sla || $sla->apply_type != 'manual') {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->ensureAuthToken('delete_sla', $security_token);

		$ticket->removeSla($sla);
		$this->em->persist($ticket);
		$this->em->flush();

		$data = array(
			'success' => true
		);

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

		$ticket_person = $ticket->person;

		$this->db->replace('tickets_deleted', array(
			'ticket_id' => $ticket->id,
			'by_person_id' => $this->person->id,
			'new_ticket_id' => 0,
			'reason' => $this->in->getString('reason'),
			'date_created' => date('Y-m-d H:i:s')
		));

		$this->em->getConnection()->beginTransaction();

		if ($this->in->getBool('ban') && !$ticket_person->is_agent) {
			$ticket->getTicketLogger()->recordExtra('is_physical_delete', true);
		}

		try {
			$ticket->setStatus('hidden.deleted');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($this->in->getBool('ban') && !$ticket_person->is_agent) {
			foreach ($ticket->person->emails as $email) {
				$email_addy = strtolower($email->email);
				App::getDb()->replace('ban_emails', array(
					'banned_email' => $email_addy,
					'is_pattern' => 0
				));
			}

			$person = $ticket->person;
			$edit_manager = $this->container->getSystemService('person_edit_manager');
			$edit_manager->setPersonContext($this->person);
			$edit_manager->deleteUser($person);
		}

		$hidden_data = $this->_getHiddenBarData($ticket);

		return $this->createJsonResponse(array(
			'success' => true,
			'banned' => $this->in->getBool('ban'),
			'hidden_html' => $this->renderView('AgentBundle:Ticket:view-hidden-bar.html.twig', array(
				'ticket' => $ticket,
				'ticket_perms' => $this->_getTicketPerms($ticket),
				'ticket_deleted' => $hidden_data['ticket_deleted'],
            	'hard_delete_time' => $hidden_data['hard_delete_time'],
			))
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
		$ticket_person = $ticket->person;

		$this->em->getConnection()->beginTransaction();

		try {
			$ticket->setStatus('hidden.spam');
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($this->in->getBool('ban') && !$ticket_person->is_agent) {
			foreach ($ticket->person->emails as $email) {
				$email_addy = strtolower($email->email);
				App::getDb()->replace('ban_emails', array(
					'banned_email' => $email_addy,
					'is_pattern' => 0
				));
			}
		}

		$hidden_data = $this->_getHiddenBarData($ticket);

		return $this->createJsonResponse(array(
			'success' => true,
			'hidden_html' => $this->renderView('AgentBundle:Ticket:view-hidden-bar.html.twig', array(
				'ticket' => $ticket,
				'ticket_perms' => $this->_getTicketPerms($ticket),
				'ticket_deleted' => $hidden_data['ticket_deleted'],
            	'hard_delete_time' => $hidden_data['hard_delete_time'],
			))
		));
	}

	protected function _getHiddenBarData(\Application\DeskPRO\Entity\Ticket $ticket)
	{
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

		return array(
			'hard_delete_time' => $hard_delete_time,
			'ticket_deleted' => $ticket_deleted
		);
	}

	############################################################################
	# change-user
	############################################################################

	public function changeUserOverlayAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

		return $this->render('AgentBundle:Ticket:change-user-overlay.html.twig', array(
			'ticket' => $ticket,
		));
	}

	public function changeUserOverlayPreviewAction($ticket_id, $new_person_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');
		$new_person = $this->em->find('DeskPRO:Person', $new_person_id);
		if (!$new_person) {
			throw $this->createNotFoundException();
		}

		return $this->render('AgentBundle:Ticket:change-user-overlay-preview.html.twig', array(
			'ticket'     => $ticket,
			'new_person' => $new_person
		));
	}

	public function changeUserAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

		$old_person = $ticket->person;

		$new_person_id = $this->in->getUint('new_person_id');
		if ($new_person_id) {
			$new_person = $this->em->find('DeskPRO:Person', $new_person_id);
			if (!$new_person) {
				throw $this->createNotFoundException();
			}
		} else {
			$name = $this->in->getString('name');
			$email = $this->in->getString('email');

			if (!$email || !\Orb\Validator\StringEmail::isValueValid($email)) {
				return $this->createJsonResponse(array(
					'success' => false,
					'error' => 'Please enter a valid email address',
				));
			} elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($email)) {
				return $this->createJsonResponse(array(
					'success' => false,
					'error' => 'The email address you entered belongs to a an account in Admin > Tickets > Email Accounts. You cannot set an email account as the ticket user.',
				));
			}

			$new_person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if (!$new_person) {
				$new_person = new Person();
				$new_person->name = $name;
				$new_person->setEmail($email, true);
			}
		}

		$ticket->person = $new_person;

		$this->db->beginTransaction();
		try {
			if ($this->in->getBool('keep')) {
				$ticket->addParticipantPerson($old_person);
			}

			if (!$new_person->getId()) {
				$this->em->persist($new_person);
			}

			$this->em->persist($ticket);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'ticket_id' => $ticket['id'],
			'old_person_id' => $old_person->getId(),
			'new_person_id' => $new_person->getId()
		));
	}

	############################################################################
	# merge
	############################################################################

	public function mergeOverlayAction($ticket_id, $other_ticket_id = 0)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		if ($other_ticket_id) {
			$other_ticket = $this->getTicketOr404($other_ticket_id, 'modify_merge');
			$other_custom_fields = $field_manager->getDisplayArrayForObject($other_ticket);
		} else {
			$other_ticket = false;
			$other_custom_fields = false;
		}

		return $this->render('AgentBundle:Ticket:merge-overlay.html.twig', array(
			'ticket' => $ticket,
			'custom_fields' => $custom_fields,
			'other_ticket' => $other_ticket,
			'other_custom_fields' => $other_custom_fields
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

		$merge = new TicketMerge($this->person, $ticket, $other_ticket);

		if (!$merge->checkPersonPermission()) {
			throw $this->createNotFoundException("User does not have permission to merge these tickets");
		}

		try {
			$this->em->beginTransaction();
			$merge->merge();
			$this->em->commit();
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException("You cannot merge a ticket with itself");
		} catch (\Exception $e) {
			$this->em->rollback(false);
			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'id' => $ticket['id'],
			'old_id' => $old_ticket_id
		));
	}

	############################################################################
	# split
	############################################################################

	public function splitAction($ticket_id, $message_id = 0)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

		if ($message_id) {
			$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);
			if (!$message || $message->ticket->id != $ticket->id) {
				$message = null;
			}
		} else {
			$message = null;
		}

		return $this->render('AgentBundle:Ticket:split-overlay.html.twig', array(
			'ticket' => $ticket,
			'message' => $message
		));
	}

	public function splitSaveAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id, 'modify_merge');
		$message_ids = $this->in->getCleanValueArray('message_ids', 'uint', 'discard');
		$subject = $this->in->getString('subject');

		$split = new TicketSplit($ticket);
		$split->setPersonContext($this->person);

		try {
			$this->em->beginTransaction();
			$new_ticket = $split->split($subject, $message_ids);
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();

			throw $e;
		}

		$this->em->persist($ticket);
		if ($new_ticket) {
			$this->em->persist($new_ticket);
		}

		$this->em->flush();

		return $this->createJsonResponse(array(
			'success' => true,
			'ticket_id' => $new_ticket ? $new_ticket['id'] : null,
			'old_ticket_deleted' => false
		));
	}

	public function forwardOverlayAction($ticket_id, $message_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		if (!$message || $message->ticket->getId() != $ticket->getId()) {
			throw $this->createNotFoundException();
		}

		$date_created = clone $message->date_created;
		$date_created->setTimezone($this->person->getDateTimezone());

		$top = trim($this->renderView('DeskPRO:emails_common:ticket-fwd-out-header.html.twig', array(
			'agent'   => $this->person,
			'ticket'  => $ticket,
			'message' => $message,
		)));

		return $this->render('AgentBundle:Ticket:forward-overlay.html.twig', array(
			'ticket'  => $ticket,
			'message' => $message,
			'date_created' => $date_created,
			'top' => $top,
		));
	}

	public function forwardSendAction($ticket_id, $message_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);
		if (!$message || $message->ticket->getId() != $ticket->getId()) {
			throw $this->createNotFoundException();
		}

		$custom_message = $this->in->getString('custom_message');

		$all_raw_to   = $this->in->getCleanValueArray('to', 'str', 'str');
		$all_to_types = $this->in->getCleanValueArray('to_type', 'str', 'str');

		$tos  = array();
		$ccs  = array();
		$bccs = array();

		$helpdesk_addresses = array();

		foreach ($all_raw_to as $rowid => $to) {
			$to = trim($to);
			if (!$to) continue;

			$raw_to = \ezcMailTools::parseEmailAddresses($to);
			if (!$raw_to) continue;

			$type = isset($all_to_types[$rowid]) ? $all_to_types[$rowid] : 'to';
			switch ($type) {
				case 'to':
					$var = &$tos;
					break;
				case 'cc':
					$var = &$ccs;
					break;
				case 'bcc':
					$var = &$bccs;
					break;
				default:
					$var = &$tos;
					break;
			}

			foreach ($raw_to as $addr) {
				if ($addr->email && StringEmail::isValueValid($addr->email)) {
					if ($this->container->getEmailAccountManager()->findAccountForEmailAddress($addr->email)) {
						$helpdesk_addresses[] = $addr->email;
					} else {
						$var[$addr->email] = $addr->name;
					}
				}
			}
			unset($var);
		}

		if ($helpdesk_addresses) {
			return $this->createJsonResponse(array(
				'error'     => 'to_helpdesk_address',
				'addresses' => $helpdesk_addresses
			));
		}

		if (!$tos) {
			return $this->createJsonResponse(array('error' => 'invalid_to'));
		}

		$subject = $this->in->getString('subject');

		$message_raw = $message->getMessageFull();
		if (!$message_raw) {
			$message_raw = $message->getMessageHtml();
		}

		$date_created = clone $message->date_created;
		$date_created->setTimezone($this->person->getDateTimezone());
		$date_created = $date_created->format($this->container->getSetting('core.date_fulltime'));

		$top = trim($this->renderView('DeskPRO:emails_common:ticket-fwd-out-header.html.twig', array(
			'agent'   => $this->person,
			'ticket'  => $ticket,
			'message' => $message,
		)));

		if ($custom_message) {
			if ($top) {
				$top .= '<br/><br/>';
			}
			$top .= '<div style="font-family: \'Helvetica Neue\',​Helvetica,​Arial,​sans-serif; font-size: 13px; color: #404040; padding: 0; margin: 0;">';
			$top .= nl2br(htmlspecialchars($custom_message));

			if ($sig = $this->person->getSignatureHtml()) {
				$top .= '<br/><br/>' . $sig . '<br/><br/><br/>';
			}

			$top .= '</div>';
		}

		if ($top) {
			$top .= '<br/><br/>';
		}

		$top .= '<div style="font-family: \'Helvetica Neue\',​Helvetica,​Arial,​sans-serif; font-size: 13px; color: #404040; padding: 0; margin: 0;">';
		$top .= '--- Forwarded Message ---<br/>';
		$top .= 'From: '. $message->person->getDisplayName() .' &lt;<a href="mailto:'. $message->person->getPrimaryEmailAddress() .'">'. $message->person->getPrimaryEmailAddress() .'</a>&gt;<br/>';

		if ($ticket->email_account) {
			$from = $ticket->email_account;
			$top .= 'To: &lt;<a href="mailto:' . $from['address'] . '">' . $from['address'] . '</a>&gt;<br/>';
		}
		$top .= 'Subject: '. htmlspecialchars($ticket->subject) . '<br/>';
		$top .= 'Date: '. $date_created .'<br/>';
		$top .= '</div>';

		$message_raw = $top . '<br/><br/>' . $message_raw;

		if (strpos($message_raw, '<body') === false) {
			$message_raw = '<html><head><style>body { font-size: 13px; color: #404040; font-family: "Helvetica Neue",​Helvetica,​Arial,​sans-serif; }</style></head><body>' . $message_raw . '</body></html>';
		}

		$message_raw = $message->procInlineAttach($message_raw);

		$email = $this->container->getMailer()->createMessage();
		foreach ($tos as $k => $x) {
			$email->addTo($k, $x);
		}
		foreach ($ccs as $k => $x) {
			$email->addCc($k, $x);
		}
		foreach ($bccs as $k => $x) {
			$email->addBcc($k, $x);
		}
		$email->setBody($message_raw, 'text/html');
		$email->setSubject($subject);

		$account = null;
		if ($this->container->getSetting('core_tickets.fwd_use_account')) {
			try {
				$account = $this->container->getEmailAccountManager()->getAccount($this->container->getSetting('core_tickets.fwd_use_account'));
				if (!($account && $account->is_enabled && $account->outgoing_account)) {
					$account = null;
				}
			} catch (\OutOfBoundsException $e) {
				$account = null;
			}
		}
		if (!$account || !$account->is_enabled || !$account->outgoing_account) {
			$account = $ticket->email_account;
		}
		if (!$account || !$account->is_enabled || !$account->outgoing_account) {
			$account = $this->container->getEmailAccountManager()->getPrimaryTicketAccount();
		}

		if ($this->container->getSetting('core_tickets.fwd_use_agent_address')) {
			$from_email = $this->person->getEmailAddress();
		} else {
			$from_email = $account->getUseEmailAddress();
		}

		$from_name = $this->person->getDisplayName();

		try {
			$email->setFrom($from_email, $from_name);
		} catch (\Swift_RfcComplianceException $e) {
			KernelErrorHandler::logException($e, false);
			throw $this->createNotFoundException();
		}

		$tr = $this->container->getEmailAccountManager()->getTransportForAccount($account);
		if ($tr) {
			$email->setForceTransport($tr);
		}

		$ticketdisplay = new \Application\DeskPRO\Tickets\TicketDisplay($ticket, $this->person);

		$attach_attachments = array();
		$max = App::getSetting('core.sendemail_attach_maxsize');
		$max_embed = App::getSetting('core.sendemail_embed_maxsize');
		$size = 0;
		$attachments = $ticketdisplay->getMessageAttachments($message, true);
		if ($attachments) {
			foreach ($attachments as $attach) {
				if ($attach->is_inline && $attach->blob->filesize > $max_embed) {
					continue;
				}

				if ($size + $attach->blob->filesize > $max) {
					break;
				}

				$attach_attachments[$attach->blob->getDownloadUrl(true)] = $attach;
			}

			foreach ($attach_attachments as $src => $attach) {
				$email->attachBlob($attach->blob, $src, $attach->is_inline);
			}
		}

		$this->container->getMailer()->send($email);

		// Log the action
		$this->db->insert('tickets_logs', array(
			'ticket_id'    => $ticket->id,
			'person_id'    => $this->person->id,
			'action_type'  => 'message_forwarded',
			'details'      => serialize(array(
				'message_id'     => $message_id,
				'agent_id'       => $this->person->id,
				'agent_name'     => $this->person->getDisplayName(),
				'to'             => array_keys($tos),
				'cc'             => array_keys($ccs),
				'bcc'            => array_keys($bccs),
				'all_rec_string' => implode(', ', array_merge(array_keys($tos), array_keys($ccs), array_keys($bccs))),
				'to_string'      => implode(', ', array_keys($tos)),
				'cc_string'      => implode(', ', array_keys($ccs)),
				'bcc_string'     => implode(', ', array_keys($bccs)),
				'from_email'     => $from_email,
				'from_name'      => $from_name,
				'custom_message' => $custom_message ?: null
			)),
			'date_created' => date('Y-m-d H:i:s')
		));

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# view-raw-message
	############################################################################

	public function viewRawMessageAction($ticket_id, $message_id)
	{
		$message = $this->em->find('DeskPRO:TicketMessage', $message_id);

		$message_raw = $message->message_raw ?: '';
		if (!$message_raw) {
			$message_raw = $message->message_full;
			if (!$message_raw) {
				$message_raw = $message->message;
			}
		}

		require_once DP_ROOT.'/vendor-src/htmlpurifier/HTMLPurifier.standalone.php';

		if ($this->in->getBool('raw')) {
			$this->ensureAuthToken('view_raw', $this->in->getString('raw'));
		} else {
			$note = '<div style="font-family: sans-serif; font-size: 11px;border-bottom: 1px solid #C5C5C5; margin-bottom: 3px; padding-bottom: 3px;">This is a safe version of the raw HTML message. <a href="' . $this->generateUrl('agent_ticket_message_raw', array('ticket_id' => $ticket_id, 'message_id' => $message_id, 'raw' => App::getSession()->generateSecurityToken('view_raw'))) . '">Click here to view the original message with no modifications</a>. Note that a malicious user may have injected harmful HTML into the message and viewing the original message may result in harmful code being executed.</div>';

			$purifier = new \HTMLPurifier();
			$config = \HTMLPurifier_Config::createDefault();
			$config->set('Cache.DefinitionImpl', null);
			$config->set('Core.Encoding', 'UTF-8');
			$config->set('HTML.TidyLevel', 'none');
			// Everything but script/iframe/applet/object
			$config->set('HTML.Allowed', 'a,abbr,acronym,address,area,b,base,basefont,bdo,big,blockquote,body,br,button,caption,center,cite,code,col,colgroup,dd,del,dfn,dir,div,dl,dt,em,fieldset,font,form,frame,frameset,h1,2,h3,h4,h5,h6,head,hr,html,i,img,input,ins,kbd,label,legend,li,link,map,menu,meta,noframes,noscript,ol,optgroup,option,p,pre,q,s,samp,select,small,span,strike,strong,style,su,sup,table,tbody,td,textarea,tfoot,th,thead,title,tr,tt,u,ul,var');
			$config->set('HTML.AllowedAttributes', 'class,id,alt,title,align,border,width,height,valign,style,cellspacing,cellpadding,colspan,rowspan,bgcolor,dir,href,target,name,rel,size,type,value,src');
			$config->set('URI.DisableExternalResources', true);

			$message_raw = $note . $purifier->purify($message_raw, $config);
		}

		if (strpos($message_raw, '<body') === false) {
			$message_raw = '<html><head><style>body { font-size: 13px; color: #404040; font-family: "Helvetica Neue",​Helvetica,​Arial,​sans-serif; }</style></head><body>' . $message_raw . '</body></html>';
		}

		$message_raw = $message->procInlineAttach($message_raw);

		$res = new Response($message_raw);
		return $res;
	}

	public function viewMessageWindowAction($message_id, $type = 'normal')
	{
		$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

		if (!$message) {
			throw $this->createNotFoundException();
		}

		$ticket = $message->ticket;

		$vars = array(
			'message' => $message,
			'ticket' => $ticket,
			'type' => $type
		);

		$message_raw = $message->message_raw ?: '';
		if (!$message_raw) {
			$message_raw = $message->message_full;
			if (!$message_raw) {
				$message_raw = $message->message;
			}
		}

		switch ($type) {
			case 'raw':
				require_once DP_ROOT.'/vendor-src/htmlpurifier/HTMLPurifier.standalone.php';
				$purifier = new \HTMLPurifier();
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Cache.DefinitionImpl', null);
				$config->set('Core.Encoding', 'UTF-8');
				$config->set('HTML.TidyLevel', 'none');
				// Everything but script/iframe/applet/object
				$config->set('HTML.Allowed', 'a,abbr,acronym,address,area,b,base,basefont,bdo,big,blockquote,body,br,button,caption,center,cite,code,col,colgroup,dd,del,dfn,dir,div,dl,dt,em,fieldset,font,form,frame,frameset,h1,2,h3,h4,h5,h6,head,hr,html,i,img,input,ins,kbd,label,legend,li,link,map,menu,meta,noframes,noscript,ol,optgroup,option,p,pre,q,s,samp,select,small,span,strike,strong,style,su,sup,table,tbody,td,textarea,tfoot,th,thead,title,tr,tt,u,ul,var');
				$config->set('HTML.AllowedAttributes', 'class,id,alt,title,align,border,width,height,valign,style,cellspacing,cellpadding,colspan,rowspan,bgcolor,dir,href,target,name,rel,size,type,value,src');
				$config->set('URI.DisableExternalResources', true);
				$message_raw = $purifier->purify($message_raw, $config);
				break;

			case 'source':

				$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($message_id);

				if ($message->email_source) {
					$r = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
					$r->setRawSource($message->email_source->raw_source);
					$body_html = $r->getBodyHtml() ? $r->getBodyHtml()->getBodyUtf8() : null;
					$body_text = $r->getBodyText() ? $r->getBodyText()->getBodyUtf8() : null;

					$vars['raw_source'] = $message->email_source->raw_source;
					$vars['body_html'] = $body_html;
					$vars['body_text'] = $body_text;

					unset($r);
					$message->email_source->clearRawSource();
				}

				break;
		}

		$vars['message_raw'] = $message_raw;

		return $this->render('AgentBundle:Ticket:ticket-message-window.html.twig', $vars);
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

		if ($this->in->getUint('ticket_id')) {
			$ticket = $this->getTicketOr404($this->in->getUint('ticket_id'));

			$message = null;
			if ($this->in->getUint('message_id')) {
				$message = $this->em->getRepository('DeskPRO:TicketMessage')->find($this->in->getUint('message_id'));
			}
			if (!$message || $message->ticket != $ticket) {
				$message = $this->em->getRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($ticket);
			}
		} else {
			$ticket = new \Application\DeskPRO\Entity\Ticket();
			$message = null;

			if ($this->settings->get('core.default_ticket_dep')) {
				$ticket->setDepartmentId($this->settings->get('core.default_ticket_dep'));
			}
			if ($this->settings->get('core.default_ticket_cat')) {
				$ticket->setCategoryId($this->settings->get('core.default_ticket_cat'));
			}
			if ($this->settings->get('core.default_ticket_pri')) {
				$ticket->setPriorityId($this->settings->get('core.default_ticket_pri'));
			}
			if ($this->settings->get('core.default_ticket_work')) {
				$ticket->setWorkflowId($this->settings->get('core.default_ticket_work'));
			}
			if ($this->settings->get('core.default_prod_id')) {
				$ticket->setProductId($this->settings->get('core.default_prod_id'));
			}
		}
		
		if ($message && count($message->attachments)) {
			$attachments = array();
			foreach ($message->attachments as $attach) {
				$new_blob = clone $attach->blob;
				
				$this->em->detach($new_blob);
				
				$this->em->persist($new_blob);
				
				$this->em->flush();
				
				$attach_data = array();
				
				$attach_data['blob'] = $new_blob->toArray();
				
				$attach_data['url'] =  $new_blob->getDownloadUrl(true);
				
				$attachments[] = $attach_data;
			}
		}

		$field_manager = $this->container->getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		return $this->render('AgentBundle:Ticket:newticket.html.twig', array(
			'ticket'                 => $ticket,
			'message'                => $message,
			'attachments'            => isset($attachments) ? $attachments : null,
			'agents'                 => $agents,
			'agent_signature'        => $this->person->getSignature(),
	        'agent_signature_html'   => $this->person->getSignatureHtml(),
			'agent_teams'            => $agent_teams,
			'ticket_options'         => $ticket_options,
			'custom_fields'          => $custom_fields,
		));
	}

	public function newSaveAction()
	{
		if (!$this->person->hasPerm('agent_tickets.create')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$newticket = new \Application\AgentBundle\Form\Model\NewTicket(
			$this->em,
			$this->person
		);
		$newticket->setBlobInlineIds($this->in->getCleanValueArray('blob_inline_ids', 'uint', 'discard'));

		if (!$this->in->getBool('options.notify_user')) {
			$newticket->suppress_user_notify = true;
		}

		$formType = new \Application\AgentBundle\Form\Type\NewTicket();
		$form = $this->get('form.factory')->create($formType, $newticket);

		if ($this->get('request')->getMethod() == 'POST') {

			$action_type = $this->in->getString('options.action');
			$macro_id = Strings::extractRegexMatch('#macro:(\d+)#', $action_type, 1);
			if ($macro_id) {
				$action_type = 'macro';
			}

			$macro = null;
			if ($macro_id) {
				$macro = $this->em->find('DeskPRO:TicketMacro', $macro_id);
			}

			$factory = new ActionsFactory();
			$collection = new ActionsCollection();
			$set_status = 'awaiting_agent';

			if ($action_type != 'macro') {
				$set_status = $action_type;
			}

			if ($macro) {
				foreach ($macro->actions as $action) {
					$action = $factory->createFromInfo($action);
					if ($action) {

						if ($action instanceof AgentAction || $action instanceof AgentTeamAction || $action instanceof ReplyAction || $action instanceof ReplySnippetAction) {
							// Ignore, the replybox itself changed for these actions
						} elseif ($action instanceof StatusAction) {
							$set_status = $action->getFullStatus();
						} else {
							$collection->add($action);
						}
					}
				}
			}

			$form->handleRequest($this->get('request'));
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

				if ($check_person->is_disabled) {
					$errors['person_disabled'] = true;
				}
			} else {
				$new_email = $this->in->getString('newticket.person.email_address');
				if (!$new_email) {
					$new_email = $this->in->getString('newticket.person_input_choice');
					$newticket->person->email_address = $new_email;
				}

				if (!$new_email && !$this->in->getString('newticket.person.name')) {
					$errors['person_no_user'] = true;
				} elseif (!\Orb\Validator\StringEmail::isValueValid($new_email)) {
					$errors['person_email_address'] = true;
				} elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($new_email)) {
					$errors['person_email_address_gateway'] = true;
				}

				$check_person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($new_email);
				if ($check_person && $check_person->is_disabled) {
					$errors['person_disabled'] = true;
				}
			}

			if (!$this->in->getString('newticket.subject')) {
				$errors['subject'] = true;
			}
			if (!$this->in->getString('newticket.message')) {
				$errors['message'] = true;
			}
			if (!$this->in->getString('newticket.department_id')) {
				$errors['department_id'] = true;
			}

			if ($errors) {
				$errors = array_keys($errors);
				return $this->createJsonResponse(array('error' => true, 'error_codes' => $errors));
			}

			// - It's possible the user account pre-existed before and was validating
			// - So the act of an agent manually selecting the account to create a new ticket for them should
			// essentially validate the account.
			// - This is needed or else the ticket will be created as validating, and no emails (not even to the user) would be sent
			if (isset($check_person) && $check_person && !$check_person->id != $this->person->id && (!$check_person->is_confirmed || !$check_person->is_agent_confirmed)) {
				$check_person->is_confirmed = true;
				$check_person->is_agent_confirmed = true;

				$email = null;
				if (isset($new_email) && $new_email) {
					$email = $check_person->findEmailAddress($new_email);
				} else {
					$email = $check_person->primary_email;
				}

				if ($email) {
					$email->is_validated = true;
				}

				// Clear any $check_persons for the user to avoid potential data leaks to do with
				// validating them now
				$this->db->delete('sessions', array('person_id' => $check_person->id));
			}

			// Validate based on department...
			$validator = new \Application\AgentBundle\Validator\NewTicketValidator();
			$layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($newticket->department_id);
			$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::NEW_TICKET, $newticket->getMockTicket());
			$newticket->ticket_fields = $this->request->request->get('custom_fields', array());
			$newticket->status = $set_status;
			$validator->setLayout($layout);

			if (!$validator->isValid($newticket)) {
				$free = array();
				foreach ($validator->getErrorsInfo() as $info) {
					$free[] = $info['message'];
				}
				return $this->createJsonResponse(array('error' => true, 'error_codes' => array('free' => true), 'error_messages' => $free));
			}

			#------------------------------
			# Save
			#------------------------------

			$this->db->beginTransaction();

			try {

				$comment_type   = $this->in->getString('for_comment_type');
				$comment_id     = $this->in->getUint('for_comment_id');
				$comment_action = $this->in->getString('comment_action');
				$comment = null;

				if ($comment_id && $comment_type && $comment_action) {
					$entity = $this->_getCommentEntityName($comment_type);
					$comment = $this->em->find($entity, $comment_id);
				}

				if ($comment) {
					$newticket->setPreSaveCallback(function(\Application\DeskPRO\Entity\Ticket $ticket) use ($comment, $comment_type, $comment_id, $comment_action) {
						$ticket->getTicketLogger()->recordExtra('created_via_comment', array(
							'comment_type'          => $comment_type,
							'comment_id'            => $comment_id,
							'comment_action'        => $comment_action,
							'comment_content_id'    => $comment->getObject()->getId(),
							'comment_content_title' => $comment->getObject()->getTitle()
						));
					});
				}

				$newticket->add_cc_person = $this->in->getCleanValueArray('newticket.add_cc_person', 'uint');
				$newticket->add_cc_newperson = $this->in->getCleanValueArray('newticket.add_cc_newperson', 'raw', 'discard');

				$newticket->save();
				$ticket = $newticket->getTicket();
                                
				$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

				$ticket->getLabelManager()->setLabelsArray($labels);

				$this->em->persist($ticket);

				if ($this->in->getUint('parent_ticket_id')) {
					$parent_ticket = $this->em->find('DeskPRO:Ticket', $this->in->getUint('parent_ticket_id'));
					if ($parent_ticket) {
						$ticket->parent_ticket = $parent_ticket;
						$this->em->flush($ticket);
						$this->em->flush();
					}
				}

				$this->em->flush();

				if ($collection->countActions()) {
					$collection->apply($ticket->getTicketLogger(), $ticket, $this->person);
					$this->em->flush();
				}

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
						if ($part) {
							$this->em->persist($part);
						}
					}

					$this->em->flush();
				}

				#------------------------------
				# Add Followers
				#------------------------------

				$add_followers = $this->in->getCleanValueArray('add_followers', 'uint', 'discard');
				if ($add_followers) {
					$ticket->setParticipantAgentIds($add_followers);
					$this->em->persist($ticket);
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

				if ($comment) {
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
				'can_view' => $this->person->PermissionsManager->TicketChecker->canView($ticket),
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
			$person = $this->container->getSystemService('UsersourceManager')->findPersonByEmail($this->in->getString('email'));
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
			if ($session && $session->visitor) {
				$person->name = $session->visitor->name;
				if ($session->visitor->email) {
					$person->setEmail($session->visitor->email);
				}
			}
		}

		$api_data = $person->toApiData();

		return $this->render('AgentBundle:Ticket:newticket-person-row.html.twig', array(
			'person' => $person,
			'api_data' => $api_data,
		));
	}

	public function getTicketMessageTemplateAction($id)
	{
		$message_template = $this->em->find('DeskPRO:TicketMessageTemplate', $id);
		if (!$message_template) {
			$message_template = new \Application\DeskPRO\Entity\TicketMessageTemplate();
		}

		return $this->createJsonResponse(array(
			'id' => $message_template->getId(),
			'message' => $message_template->message,
			'subject' => $message_template->subject
		));
	}

	public function lockTicketAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if ($ticket->hasLock()) {
			return $this->createJsonResponse(array(
				'error' => true,
			));
		}

		$lock_cm = new ClientMessage();
		$lock_cm->fromArray(array(
			'channel' => 'agent-notification.tickets.locked',
			'data' => array(
				'ticket_id' => $ticket['id'],
				'agent_id' => $ticket['id'],
			),
			'created_by_client' => 0,
		));
		$this->em->persist($lock_cm);

		$ticket->setLockedByAgent($this->person);
		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	public function unlockTicketAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if (!$ticket->hasLock()) {
			return $this->createJsonResponse(array('success' => true));
		}

		$lock_cm = new ClientMessage();
		$lock_cm->fromArray(array(
			'channel' => 'agent-notification.tickets.unlocked',
			'data' => array(
				'ticket_id' => $ticket['id'],
				'agent_id' => $ticket['id'],
			),
			'created_by_client' => 0,
		));
		$this->em->persist($lock_cm);

		$ticket->setLockedByAgent(null);
		$this->em->persist($ticket);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	public function releaseLockAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if ($ticket->hasLock() && $ticket->locked_by_agent->id == $this->person->id) {
			$lock_cm = new ClientMessage();
			$lock_cm->fromArray(array(
				'channel' => 'agent-notification.tickets.unlocked',
				'data' => array(
					'ticket_id' => $ticket['id'],
					'agent_id' => $ticket['id'],
				),
				'created_by_client' => 0,
			));
			$this->em->persist($lock_cm);

			$ticket->setLockedByAgent(null);
			$this->em->persist($ticket);
			$this->em->flush();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function updateDraftsAction()
	{
		$ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');

		$tickets = $this->em->getRepository('DeskPRO:Ticket')->getByIds($ticket_ids);
		$drafts = $this->em->getRepository('DeskPRO:Draft')->getActiveDrafts('ticket', $ticket_ids);

		$output = array();
		foreach ($tickets AS $ticket) {
			if (empty($drafts[$ticket->id])) {
				continue;
			}
			if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
				continue;
			}

			$output[$ticket->id] = $this->_renderActiveDrafts($ticket, $drafts[$ticket->id]);
		}

		return $this->createJsonResponse(array(
			'drafts' => $output
		));
	}

	protected function _renderActiveDrafts(\Application\DeskPRO\Entity\Ticket $ticket, array $drafts)
	{
		$output = array();

		unset($drafts[$this->person->id]);
		foreach ($drafts AS $id => $draft) {
			$output[] = $this->renderView('AgentBundle:Ticket:ticket-message-draft.html.twig', array(
				'draft' => $draft,
				'ticket' => $ticket
			));
		}

		return $output;
	}

	############################################################################
	# download-ticket-debug
	############################################################################

	public function downloadTicketDebugAction($ticket_id)
	{
		if (!$this->person->can_admin) {
			throw $this->createNotFoundException();
		}

		$ticket = $this->getTicketOr404($ticket_id);

		$tmpdir = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . "ticket-debug-" . $ticket->id . "_" . date('YmdHis') . "_" . Strings::random(4, Strings::CHARS_ALPHANUM_IU);
		if (!mkdir($tmpdir, 0777, true)) {
			echo "Could not create temp dir: " . $tmpdir;
			exit;
		}

		$d = new TicketTriggerData();
		file_put_contents($tmpdir . '/triggers.json', json_encode($d->getData()));

		$d = new TicketFilterData();
		file_put_contents($tmpdir . '/filters.json', json_encode($d->getData()));

		$d = new TicketData($ticket);
		file_put_contents($tmpdir . '/ticket.json', json_encode($d->getData()));

		$d = new TicketPersonData($ticket);
		file_put_contents($tmpdir . '/person.json', json_encode($d->getData()));

		$d = new TicketLogsData($ticket);
		file_put_contents($tmpdir . '/ticket-log.json', json_encode($d->getData()));

		foreach ($ticket->messages as $message) {
			$data = $message->toApiData();

			if (count($message->attachments)) {
				$data['attachments'] = array();
				foreach ($message->attachments as $attach) {
					$attach_data = $attach->toArray();
					unset($attach_data['ticket'], $attach_data['message'], $attach_data['person'], $attach_data['visitor']);
					$attach_data['blob'] = $attach->blob->toArray();
					$data['attachments'][] = $attach_data;
				}
			}

			file_put_contents($tmpdir . '/message-'.$message->id.'.json', json_encode($data));

			if ($message->email_source && $message->email_source->blob) {
				try {
					$this->container->getBlobStorage()->copyBlobRecordToFile($tmpdir . '/message-' . $message->id . '-source.eml', $message->email_source->blob);
				} catch (\Exception $e) {
					file_put_contents($tmpdir . '/message-' . $message->id . '-source.eml', "Could not download blob: {$e->getMessage()}");
				}
			}

			if ($message->email_source && $message->email_source->source_info) {
				file_put_contents($tmpdir . '/message-'.$message->id.'.source_info.txt', $message->email_source->getSourceInfoAsString());
			}

			if ($message->email_source && $message->email_source->log_blob) {
				$this->container->getBlobStorage()->copyBlobRecordToFile($tmpdir . '/message-'.$message->id.'.log', $message->email_source->log_blob);
			}
		}

		$tm_logs = $this->em->createQuery("
			SELECT tm_log, b
			FROM DeskPRO:TicketProcLog tm_log
			LEFT JOIN tm_log.blob b
			WHERE tm_log.ticket = ?0
		")->execute(array($ticket));

		foreach ($tm_logs as $tm_log) {
			try {
				$this->container->getBlobStorage()->copyBlobRecordToFile($tmpdir . '/' . $tm_log->blob->filename, $tm_log->blob);
			} catch (\Exception $e) {
				file_put_contents($tmpdir . '/message-' . $message->id . '-source.eml', "Could not download blob: {$e->getMessage()}");
			}
		}

		$outfile = $tmpdir.'/zip';

		require_once(DP_ROOT . '/vendor-src/pclzip/pclzip.lib.php');
		$zip = new \PclZip($outfile);
		$zip->add(
			$tmpdir,
			\PCLZIP_OPT_REMOVE_PATH, dirname($tmpdir)
		);

		header('Content-Type: application/zip; filename=ticket-debug-' . $ticket->id . '.zip');
		header('Content-Length: ' . filesize($outfile));
		header('Content-Disposition: attachment; filename=ticket-debug-' . $ticket->id . '.zip');

		$fp = fopen($outfile, 'r');
		while (!feof($fp)) {
			echo fread($fp, 1024);
		}
		fclose($fp);

		unlink($outfile);
		$fs = new Filesystem();
		$fs->remove($tmpdir);
		exit;
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

	public function permCheckArray($tickets, $check_perm)
	{
		if ($tickets instanceof ArrayCollection) {
			$tickets = $tickets->toArray();
		}

		$self = $this;
		return array_filter($tickets, function($t) use ($self, $check_perm) {
			return $self->checkPerm($t, $check_perm);
		});
	}

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id, $check_perm = null)
	{
		$q = $this->em->createQuery("
			SELECT t, person, person_primary_email, agent,
				agent_team, language, department, product, category, workflow, priority,
				organization, locked_by_agent
			FROM DeskPRO:Ticket t
			LEFT JOIN t.person person
			LEFT JOIN person.primary_email person_primary_email
			LEFT JOIN t.agent agent
			LEFT JOIN t.agent_team agent_team
			LEFT JOIN t.language language
			LEFT JOIN t.department department
			LEFT JOIN t.product product
			LEFT JOIN t.category category
			LEFT JOIN t.workflow workflow
			LEFT JOIN t.priority priority
			LEFT JOIN t.organization organization
			LEFT JOIN t.locked_by_agent locked_by_agent
			WHERE t.id = ?0
		");
		$q->setParameters(array($ticket_id));

		$ticket = $q->getOneOrNullResult();

		// If no ticket, check the delete log in case it was merged since
		if (!$ticket) {
			$merged_ticket_id = $this->em->getRepository('DeskPRO:Ticket')->findTicketId($ticket_id);
			if ($merged_ticket_id) {
				return $this->getTicketOr404($merged_ticket_id, $check_perm);
			}
		}

		if (!$ticket) {
			throw $this->createNotFoundException("There is no ticket with ID $ticket_id");
		}

		if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
			throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("You are not allowed to view this ticket");
		}

		if ($check_perm && !$this->checkPerm($ticket, $check_perm)) {
			throw new \Application\DeskPRO\HttpKernel\Exception\NoPermissionException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
	
	public function linkExistingAction($ticket_id, $linked_ticket_id)
	{
		try	{
			$ticket = $this->getTicketOr404($ticket_id);
		} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			// try to find a delete log
			$delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
			if ($delete_log) {
				return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
			} else {
				throw $e;
			}
		}
		
		try	{
			$linkedTicket = $this->getTicketOr404($linked_ticket_id);
		} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			// try to find a delete log
			$delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $linked_ticket_id));
			if ($delete_log) {
				return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
			} else {
				throw $e;
			}
		}
		
		if ($this->in->getBool('isParent')) {
			$ticket->parent_ticket = $linkedTicket;
		} else {
			$linkedTicket->parent_ticket = $ticket;
		}
		
		$this->em->persist($ticket);
		$this->em->persist($linkedTicket);
		
		$this->em->flush();
		
		return $this->createJsonResponse(array('success' => 1));
		
	}
	
	public function linkExistingOverlayAction($ticket_id)
	{
		try	{
			$ticket = $this->getTicketOr404($ticket_id);
		} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			// try to find a delete log
			$delete_log = $this->em->getRepository('DeskPRO:TicketDeleted')->findOneBy(array('ticket_id' => $ticket_id));
			if ($delete_log) {
				return $this->render('AgentBundle:Ticket:deleted.html.twig', array('delete_log' => $delete_log));
			} else {
				throw $e;
			}
		}
		
		return $this->render('AgentBundle:Ticket:link.html.twig');
	}

	public function unlinkTicketAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		switch ($this->in->getString('link_type')) {
			case 'parent':
				$linked_ticket = $ticket;
				break;

			case 'child':
			case 'sibling':
				$linked_ticket = $this->em->find('DeskPRO:Ticket', $this->in->getUint('link_ticket_id'));
				break;
		}

		if (!$linked_ticket) {
			throw $this->createNotFoundException();
		}

		$linked_ticket->parent_ticket = null;
		$this->em->persist($linked_ticket);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}
}
