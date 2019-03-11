<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewTicket;
use Application\AgentBundle\Validator\NewTicketValidator;
use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Debug\Data\TicketContextData;
use Application\DeskPRO\Debug\Data\TicketData;
use Application\DeskPRO\Debug\Data\TicketFilterData;
use Application\DeskPRO\Debug\Data\TicketLayoutsData;
use Application\DeskPRO\Debug\Data\TicketLogsData;
use Application\DeskPRO\Debug\Data\TicketPersonData;
use Application\DeskPRO\Debug\Data\TicketTriggerData;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\Draft;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketCharge;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\Entity\TicketFeedback;
use Application\DeskPRO\Entity\TicketFlagged;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketMessageTranslated;
use Application\DeskPRO\EventDispatcher\PropertyChangedCallback;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\Settings\EmailAccountsSettings;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\AgentAction;
use Application\DeskPRO\Tickets\TicketActions\AgentTeamAction;
use Application\DeskPRO\Tickets\TicketActions\ReplyAction;
use Application\DeskPRO\Tickets\TicketActions\ReplySnippetAction;
use Application\DeskPRO\Tickets\TicketActions\StatusAction;
use Application\DeskPRO\Tickets\TicketDisplay;
use Application\DeskPRO\Tickets\TicketMerge\TicketMerge;
use Application\DeskPRO\Tickets\Tickets;
use Application\DeskPRO\Tickets\TicketSplit;
use Application\EmailBundle\SwiftMailer\Message\MessageOptionsInterface;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Component\Pdf\PdfRendererInterface;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Handles ticket searches.
 */
class TicketController extends AbstractController
{
    /**
     * See _getTicketPerms.
     *
     * @var array
     */
    private $ticketPermsCache = [];

    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'viewRawMessageAction') {
            return false;
        }

        return parent::requireRequestToken($action, $arguments);
    }

    //###########################################################################
    // view
    //###########################################################################

    /**
     * @param Ticket $entity
     *
     * @throws \Exception
     *
     * @return array mixed
     */
    protected function getAPIv2Data($entity)
    {
        $context = new SideloadSerializationContext();
        $context->setIncludes(['brand', 'person', 'organization', 'problem']);
        $context->setInlineSideloads(true);
        $serialized = $this->container->get('serializer')->toArray(new ApiWrapper($entity), $context);

        return $serialized;
    }

    /**
     * @param int $ticket_id
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     *
     * @return Response
     */
    public function viewAction($ticket_id)
    {
        $isPdf    = $this->in->getBool('pdf');
        $is_print = $this->in->getBool('view_print');

        try {
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository(TicketDeleted::class)->findOneBy(['ticket_id' => $ticket_id]);
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', ['delete_log' => $delete_log]);
            } else {
                throw $e;
            }
        }

        /** @var Tickets $ticketsService */
        $ticketsService = App::getApi('tickets');
        $ticket_options = $ticketsService->getTicketOptions($this->person);

        $ticket_attachments = $this->em->getRepository(TicketAttachment::class)->getTicketAttachments($ticket);
        if (!$ticket_attachments) {
            $ticket_attachments = [];
        }

        $organization = null;
        if ($ticket->getPerson()) {
            $organization = $ticket->getPerson()->getOrganization();
        }

        //------------------------------
        // Custom fields
        //------------------------------

        $layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
            $ticket->getDepartmentId()
        );
        $layout      = LayoutDisplay::createFromLayout($layout, LayoutDisplay::EDIT_TICKET, $ticket);
        $view_layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);

        $field_manager        = $this->container->getTicketFieldManager();
        $person_field_manager = $this->container->getPersonFieldManager();
        $org_field_manager    = $this->container->getOrgFieldManager();

        $custom_fields        = $field_manager->getDisplayArrayForObject($ticket);
        $person_fields_group  = $this->get('form.factory')->createNamedBuilder('custom_person_fields', 'form');
        $custom_person_fields = $person_field_manager->getDisplayArrayForObject(
            $ticket->getPerson(),
            $person_fields_group
        );
        $org_fields_group  = $this->get('form.factory')->createNamedBuilder('custom_org_fields', 'form');
        $custom_org_fields = $organization
            ? $org_field_manager->getDisplayArrayForObject($organization, $org_fields_group)
            : [];

        if (App::getSetting('core_tickets.enable_billing') || App::getSetting('core_tickets.enable_timelog')) {
            $billing_field_manager = $this->container->getBillingFieldManager();
            $billing_fields_new    = $billing_field_manager->getDisplayArrayForObject(new Entity\TicketCharge());
            $billing_fields        = [];

            foreach ($ticket->charges as $charge) {
                $billing_fields[$charge['id']] = $billing_field_manager->getDisplayArrayForObject($charge);
            }
        }

        // new custom fields
        $new_field_manager = $this->container->getCustomFieldManager();
        $new_custom_fields = $new_field_manager->createFormForOwner(
            $ticket,
            $ticket->getPerson(),
            null,
            ['allow_edit' => true]
        );
        if ($organization) {
            $new_field_manager->merge(
                $new_custom_fields,
                $new_field_manager->createFormForOwner($ticket, $organization, null, ['allow_edit' => true])
            );
        }

        //------------------------------
        // Messages
        //------------------------------

        $ticket_messages_blockcache = $this->_getMessageBlockInfo($ticket, 1, $ticket_attachments, $isPdf, $is_print);
        $ticket_messages_block      = $ticket_messages_blockcache['ticket_messages_block'];
        $ticket_attachments         = $ticket_messages_blockcache['ticket_attachments'];
        $ticket_message_attachments = isset($ticket_messages_blockcache['ticket_message_attachments']) ? $ticket_messages_blockcache['ticket_message_attachments'] : [];
        $counts['messages']         = $ticket_messages_blockcache['message_count'];

        $ticket_flagged = $this->em->getRepository(TicketFlagged::class)->getFlagForTicket($ticket, $this->person);

        $macros      = $this->person->Agent->getMacros();
        $hidden_data = $this->_getHiddenBarData($ticket);

        // Check if the search adapter
        $show_related_content = false;

        $participants = $ticket->getParticipants();

        $participant_ids = [];
        $agent_parts     = [];
        $user_parts      = [];

        foreach ($participants as $participant) {
            $participantPerson   = $participant->getPerson();
            $participantPersonId = $participantPerson->getId();

            $participant_ids[$participantPersonId] = $participantPersonId;

            if ($participantPerson->isAgent()) {
                $agent_parts[$participantPersonId] = $participant;
            } else {
                $user_parts[$participantPersonId] = $participant;
            }
        }

        $agent_teams = $this->container->getDataService('AgentTeam')->getTeams();

        //------------------------------
        // Linked tasks
        //------------------------------

        $tasks = $this->em->getRepository(Task::class)->findLinkedTicketTasks($ticket, $this->person, true);
        usort(
            $tasks,
            function ($a, $b) {
                $a_time = $a->date_due ? $a->date_due->getTimestamp() : 0;
                $b_time = $b->date_due ? $b->date_due->getTimestamp() : 0;

                if ($a_time == $b_time) {
                    return 0;
                }

                return ($a_time < $b_time) ? -1 : 1;
            }
        );

        $addable_slas = $this->em->getRepository(Sla::class)->getAddableSlas($ticket);

        $draft = $this->em->getRepository(Draft::class)->getDraft('ticket', $ticket->getId());
        if ($draft && !empty($draft->extras['attach'])) {
            $draft_attachments = $this->em->getRepository(Blob::class)->getByIds($draft->extras['attach'], true);
        } else {
            $draft_attachments = [];
        }

        $active_drafts = $this->em->getRepository(Draft::class)->getActiveDrafts('ticket', $ticket->getId());
        unset($active_drafts[$this->person->getId()]);

        $edit_person = $this->person->hasPerm('agent_people.edit');
        if ($edit_person) {
            if (!$this->person->canAdmin() && $ticket->getPerson()->isAgent() && $ticket->getPerson(
                ) !== $this->person
            ) {
                $edit_person = false;
            }
        }

        //------------------------------
        // Validate a ticket to see if we need to lock the reply form
        //------------------------------

        $newticket = new NewTicket($this->em, $this->person);
        $newticket->setValuesFromTicket($ticket);

        $validator = new NewTicketValidator();
        $layout    = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
            $newticket->department_id ?: 0
        );
        $layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::NEW_TICKET);
        $validator->setLayout($layout);

        $validator_errors = [];
        if (!$validator->isValid($newticket)) {
            foreach ($validator->getErrorsInfo() as $info) {
                $validator_errors[] = htmlspecialchars($info['message']);
            }
        }

        //------------------------------
        // Linked tickets
        //------------------------------

        $linked_tickets = [
            'parent'   => null,
            'siblings' => [],
            'children' => [],
            'count'    => 0,
        ];

        $ticketRepo   = $this->em->getRepository(Ticket::class);
        $parentTicket = $ticket->getParentTicket();
        if ($parentTicket && $parentTicket->getStatus() !== 'hidden' && $this->checkPerm($parentTicket, 'view')) {
            $linked_tickets['parent'] = $parentTicket;

            // Find siblings
            $linked_tickets['siblings'] = $this->permCheckArray(
                $ticketRepo->getLinkedTickets($parentTicket),
                'view'
            );
            $linked_tickets['siblings'] = array_filter(
                $linked_tickets['siblings'],
                function ($t) use ($ticket) {
                    if ($t->id == $ticket->getId()) {
                        return false;
                    } else {
                        return true;
                    }
                }
            );
        }

        $linked_tickets['children'] = $this->permCheckArray(
            $ticketRepo->getLinkedTickets($ticket),
            'view'
        );

        $linked_tickets['count'] = array_sum(
            [
                $linked_tickets['parent'] ? 1 : 0,
                count($linked_tickets['siblings']),
                count($linked_tickets['children']),
            ]
        );

        //------------------------------
        // Linked Feedback
        //------------------------------
        $feedbackRepo        = $this->em->getRepository(TicketFeedbackLink::class);
        $ticketFeedbackLinks = $feedbackRepo->findByTicketAndJoinFeedbackData($ticket);

        //------------------------------
        // Pre-load person and org
        //------------------------------

        $open_problems = [];
        $incidents     = 0;
        if ($this->person->hasPerm('agent_problems.view')) {
            $open_problems = $this->em->getRepository(Problem::class)->findBy(
                ['is_open' => true],
                ['title' => 'asc']
            );

            if ($problem = $ticket->getProblems()->first()) {
                $rep            = $this->em->getRepository(Problem::class);
                $problem_counts = $rep->getCountsForAgentInterface([$problem], $this->person);
                $incidents      = (int) @$problem_counts[$problem->id];
            }
        }

        $brands      = $this->em->getRepository(Brand::class)->findAll();
        $chatChecker = $this->getPerson()->getPermissionsManager()->ChatChecker;
        $linkedChat  = null;
        if ($ticket->linked_chat && $chatChecker->canView($ticket->linked_chat)) {
            $linkedChat = $ticket->linked_chat;
        }

        $vars = [
            'agent_teams' => $agent_teams,
            'tasks'       => $tasks,
            'brands'      => $brands,

            'ticket_perms'               => $this->_getTicketPerms($ticket),
            'ticket'                     => $ticket,
            'ticket_attachments'         => $ticket_attachments,
            'ticket_message_attachments' => $ticket_message_attachments,
            'linked_chat'                => $linkedChat,

            'ticket_permalink' => $this->get('router')->generate(
                'go_to_ticket_id',
                ['id' => $ticket->getId()],
                Router::ABSOLUTE_URL
            ),

            'validator_errors' => $validator_errors,

            'draft'             => $draft,
            'draft_attachments' => $draft_attachments,
            'active_drafts'     => $active_drafts,

            'edit_person' => $edit_person,

            'last_message_id'    => $ticket_messages_blockcache['last_message_id'],
            'message_count'      => $ticket_messages_blockcache['message_count'],
            'message_page_count' => $ticket_messages_blockcache['message_page_count'],
            'message_page'       => $ticket_messages_blockcache['message_page'],

            'participants'    => $participants,
            'participant_ids' => $participant_ids,
            'agent_parts'     => $agent_parts,
            'user_parts'      => $user_parts,

            'custom_fields'        => $custom_fields,
            'new_custom_fields'    => $new_custom_fields->createView(),
            'custom_person_fields' => $custom_person_fields,
            'custom_org_fields'    => $custom_org_fields,

            'show_related_content'  => $show_related_content,
            'linked_tickets'        => $linked_tickets,
            'ticket_feedback_links' => $ticketFeedbackLinks,

            'ticket_messages_block' => $ticket_messages_block,

            'ticket_deleted'   => $hidden_data['ticket_deleted'],
            'hard_delete_time' => $hidden_data['hard_delete_time'],
            'ticket_options'   => $ticket_options,
            'ticket_flagged'   => $ticket_flagged,
            'macros'           => $macros,

            'agent_signature'      => $this->person->getSignature(),
            'agent_signature_html' => $this->person->getSignatureHtml(),

            'addable_slas'         => $addable_slas,
            'person_object_counts' => $this->em->getRepository(Person::class)->getPersonObjectCounts(
                $ticket->getPerson()
            ),
            'open_problems'  => $open_problems,
            'incidents'      => $incidents,
            'system_account' => $this->getAccount($ticket),
            'person_repo'    => $this->em->getRepository(Person::class),
        ];

        // include api_v2_data
        $vars['api_v2_data'] = $this->getAPIv2Data($ticket);

        if (App::getSetting('core_tickets.enable_billing') || App::getSetting('core_tickets.enable_timelog')) {
            $vars['billing_fields_new'] = $billing_fields_new;
            $vars['billing_fields']     = $billing_fields;
        }

        if ($isPdf) {
            $vars['layout'] = $view_layout;
            $contentHtml    = $this->renderView('DeskPRO:pdf_agent:view_ticket.html.twig', $vars);

            /** @var PdfRendererInterface $pdfRenderer */
            $pdfRenderer = $this->get('pdf_renderer');

            $pdf = $pdfRenderer->render($contentHtml);

            $response = new Response();

            if ($this->in->getBool('html')) {
                $response->setContent($contentHtml);
            } else {
                $response->setContent($pdf);
                $response->headers->set('Content-Disposition', 'attachment; filename=Ticket_'.$ticket->id.'.pdf');
                $response->headers->set('Content-Type', 'application/pdf');
            }

            return $response;
        }

        if ($is_print) {
            $vars['print']  = true;
            $vars['layout'] = $view_layout;

            return $this->render('DeskPRO:pdf_agent:view_ticket.html.twig', $vars);
        }

        if (App::getSetting('core_tickets.lock_on_view') && !$ticket->hasLock()) {
            $ticket->setLockedByAgent($this->person);
            $this->em->persist($ticket);
            $this->em->flush();
        }

        $vars['ticket_statuses'] = App::getContainer()->getTicketStatuses()->getTopLevelStatuses(true);

        return $this->render('AgentBundle:Ticket:view.html.twig', $vars);
    }

    /**
     * @param int     $ticket_id
     * @param Request $request
     *
     * @return Response
     */
    public function viewTicketPersonAction($ticket_id, Request $request)
    {
        $ticket = $this->getTicketOr404($ticket_id);
        $vars   = [
            'ticket' => $ticket,
        ];

        if ($request->get('select_person')) {
            return $this->render('AgentBundle:Ticket:select-user-menu.html.twig', $vars);
        } else {
            return $this->render('AgentBundle:Ticket:view-ticket-person-holder.html.twig', $vars);
        }
    }

    /**
     * @param int $ticket_id
     * @param int $page
     *
     * @return Response
     */
    public function getMessagePageAction($ticket_id, $page)
    {
        if (!(int) $page) {
            return $this->createResponse(sprintf('Incorrect page number: %s', $page), Response::HTTP_BAD_REQUEST);
        }

        $ticket                   = $this->getTicketOr404($ticket_id);
        $ticketMessagesBlockCache = $this->_getMessageBlockInfo($ticket, $page);

        return $this->createResponse($ticketMessagesBlockCache['ticket_messages_block'], Response::HTTP_OK);
    }

    protected function _getTicketPerms(Entity\Ticket $ticket)
    {
        if (isset($this->ticketPermsCache[$ticket->getId()])) {
            return $this->ticketPermsCache[$ticket->getId()];
        }
        $ticket_perms                        = [];
        $ticket_perms['delete']              = $this->person->PermissionsManager->TicketChecker->canDelete($ticket);
        $ticket_perms['reply']               = $this->person->PermissionsManager->TicketChecker->canReply($ticket);
        $ticket_perms['modify_set_archived'] = $this->person->PermissionsManager->TicketChecker->canSetArchived(
            $ticket
        );

        foreach ([
                     'department',
                     'slas',
                     'fields',
                     'assign_agent',
                     'assign_team',
                     'assign_self',
                     'cc',
                     'merge',
                     'labels',
                     'notes',
                     'set_hold',
                     'set_awaiting_agent',
                     'set_awaiting_user',
                     'set_resolved',
                     'set_unresolved',
                     'billing',
                     'followed',
                 ] as $p) {
            $ticket_perms["modify_$p"] = $this->person->PermissionsManager->TicketChecker->canModify($ticket, $p);
        }

        $ticket_perms['modify_messages'] = $this->person->PermissionsManager->TicketChecker->canEditMessages($ticket);

        $this->ticketPermsCache[$ticket->getId()] = $ticket_perms;

        return $ticket_perms;
    }

    public function loadTicketLogsAction($ticket_id)
    {
        $page       = $this->in->getUInt('page') ?: 1;
        $filter     = $this->in->getString('filter');
        $up_to_page = $this->in->getBool('up_to_page');

        $ticket = $this->getTicketOr404($ticket_id);

        $info = $this->_getTicketLogsBlockInfo($ticket, $page, $filter == 'all' ? null : $filter, $up_to_page);

        return $this->createResponse($info['rendered']);
    }

    public function loadAttachListAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        $ticket_attachments = $this->em->getRepository(TicketAttachment::class)->getTicketAttachments($ticket);
        $attach_to_message  = [];

        foreach ($ticket_attachments as $attach) {
            if ($attach->message) {
                $attach_to_message[$attach->id] = $attach->message;
            }
        }

        $all_ticket_logs  = $this->em->getRepository(TicketLog::class)->getLogsForTicket($ticket, []);
        $counts           = $this->em->getRepository(TicketLog::class)->countTicketLogTypes($all_ticket_logs);
        $counts['attach'] = count($ticket_attachments);

        return $this->render(
            'AgentBundle:Ticket:ticket-attach-list.html.twig',
            [
                'ticket'            => $ticket,
                'filter'            => 'attach',
                'counts'            => $counts,
                'attachments'       => $ticket_attachments,
                'attach_to_message' => $attach_to_message,
            ]
        );
    }

    protected function _getTicketLogsBlockInfo(
        Ticket $ticket,
        $page = 1,
        $filter = null,
        $up_to_page = false,
        $per_page = null
    ) {
        if (!$per_page) {
            if ($filter) {
                // 50 when filtered because entries are "loose"
                $per_page = 50;
            } else {
                // Only 10 when not filtered because entries are grouped,
                // so 10 is typically more like 50
                $per_page = 10;
            }
        }

        $options         = [];
        $all_ticket_logs = $this->em->getRepository(TicketLog::class)->getLogsForTicket($ticket, $options);

        $counts           = $this->em->getRepository(TicketLog::class)->countTicketLogTypes($all_ticket_logs);
        $counts['attach'] = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM tickets_attachments WHERE ticket_id = ?',
            [$ticket->id]
        );

        if ($filter) {
            $all_ticket_logs = $this->em->getRepository(TicketLog::class)->filterTicketLogs($all_ticket_logs, $filter);
        } else {
            $all_ticket_logs = $this->em->getRepository(TicketLog::class)->groupTicketLogs($all_ticket_logs);
        }

        $all_ticket_logs = array_chunk($all_ticket_logs, $per_page, true);

        if ($up_to_page) {
            $ticket_logs = [];
            for ($i = 0; $i < $page; ++$i) {
                $p           = isset($all_ticket_logs[$page - 1]) ? $all_ticket_logs[$page - 1] : [];
                $ticket_logs = array_merge($ticket_logs, $p);
            }
        } else {
            $ticket_logs = isset($all_ticket_logs[$page - 1]) ? $all_ticket_logs[$page - 1] : [];
        }

        // Get email status
        $sendmail_source_ids = [];
        foreach ($ticket_logs as $l) {
            if (!empty($l['details']['sendmail_source_id'])) {
                $sendmail_source_ids[] = $l['details']['sendmail_source_id'];
            } elseif (!empty($l['grouped'])) {
                foreach ($l['grouped'] as $l2) {
                    if (!empty($l2['details']['sendmail_source_id'])) {
                        $sendmail_source_ids[] = $l2['details']['sendmail_source_id'];
                    }
                }
            }
        }

        if ($sendmail_source_ids) {
            $sendmail_source_status = $this->db->fetchAllKeyed(
                '
                SELECT id, status
                FROM sendmail_sources
                WHERE id IN (?)
            ',
                [$sendmail_source_ids],
                'id',
                [Connection::PARAM_INT_ARRAY]
            );
        } else {
            $sendmail_source_status = [];
        }

        $info                           = [];
        $info['ticket']                 = $ticket;
        $info['ticket_perms']           = $this->_getTicketPerms($ticket);
        $info['num_pages']              = count($all_ticket_logs);
        $info['cur_page']               = $page;
        $info['ticket_logs']            = $ticket_logs;
        $info['filter']                 = $filter;
        $info['counts']                 = $counts;
        $info['sendmail_source_status'] = $sendmail_source_status;

        $rendered         = $this->renderView('AgentBundle:Ticket:ticket-logs.html.twig', $info);
        $info['rendered'] = $rendered;

        return $info;
    }

    protected function _getMessageBlockInfo(
        Ticket $ticket,
        $page,
        array $ticket_attachments = [],
        $is_pdf = false,
        $is_print = false
    ) {
        $per_page = ($is_pdf || $is_print) ? 500 : 25;

        $all_message_ids = $this->db->fetchAllCol(
            '
            SELECT id
            FROM tickets_messages
            WHERE ticket_id = ?
            ORDER BY date_created DESC
        ',
            [$ticket->getId()]
        );

        $message_numbers = [];
        if ($all_message_ids) {
            $message_numbers = array_combine(
                array_values($all_message_ids),
                array_reverse(array_keys($all_message_ids))
            );
        }

        $message_count = count($all_message_ids);
        $num_pages     = ceil($message_count / $per_page);

        $message_ids = array_slice($all_message_ids, ($page - 1) * $per_page, $per_page);

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $ticketMessageRepo */
        $ticketMessageRepo = $this->em->getRepository(TicketMessage::class);
        /** @var TicketMessage[] $ticket_messages */
        $ticket_messages = $ticketMessageRepo->getByIds($message_ids);

        usort(
            $ticket_messages,
            function (TicketMessage $a, TicketMessage $b) {
                $ts_a = $a->getDateCreated()->getTimestamp();
                $ts_b = $b->getDateCreated()->getTimestamp();

                if ($ts_a == $ts_b) {
                    return 0;
                }

                return ($ts_a < $ts_b) ? -1 : 1;
            }
        );

        if (empty($ticket_attachments)) {
            $ticket_attachments = $this->em->getRepository(TicketAttachment::class)->getAttachmentsForMessages(
                $ticket_messages
            );
        }

        $ticket_messages_translated = $this->em->getRepository(TicketMessageTranslated::class)->getForMessages(
            $ticket_messages,
            $this->person->getLanguage()->getLocale()
        );

        foreach ($ticket_messages as $message) {
            $messageId = $message->getId();
            if (!isset($ticket_messages_translated[$messageId]) && $message->getPrimaryTranslation()) {
                $ticket_messages_translated[$messageId] = $message->getPrimaryTranslation();
            }
        }

        // Group attachments into messages so we can place them into each message
        $ticket_message_attachments = [];
        foreach ($ticket_attachments as $attach) {
            if (!$attach['message'] || $attach['is_inline']) {
                continue;
            }
            if (!isset($ticket_message_attachments[$attach['message']['id']])) {
                $ticket_message_attachments[$attach['message']->getId()] = [];
            }

            $ticket_message_attachments[$attach['message']->getId()][] = $attach->getId();
        }

        $last_message_id = 0;

        $ticket_messages_num = [];
        foreach ($ticket_messages as $message) {
            $messageId = $message->getId();

            $ticket_messages_num[$messageId] = $message_numbers[$messageId] + 1;

            if ($messageId > $last_message_id) {
                $last_message_id = $messageId;
            }
        }

        $ticket_messages_block = '';

        $all_feedback = $this->em->getRepository(TicketFeedback::class)->getFeedbackForTicket($ticket);

        // Ticket logs to do with forwarded messages
        $fwd_logs = $this->em->createQuery(
            "
            SELECT log, person
            FROM DeskPRO:TicketLog log
            LEFT JOIN log.person person
            WHERE log.ticket = ?0 AND log.action_type = 'message_forwarded'
        "
        )->execute([$ticket]);

        $ticket_fwd_logs = [];
        foreach ($fwd_logs as $log) {
            $mid = $log->details['message_id'];
            if (!isset($ticket_fwd_logs[$mid])) {
                $ticket_fwd_logs[$mid] = [];
            }

            $ticket_fwd_logs[$mid][] = $log;
        }

        if ($ticket_messages) {
            if ($is_pdf || $is_print) {
                $tpl = 'DeskPRO:pdf_agent:ticket-messages-batch.html.twig';
            } else {
                $tpl = 'AgentBundle:Ticket:ticket-messages-batch.html.twig';
            }
            $ticket_messages_block = $this->renderView(
                $tpl,
                [
                    'ticket'                     => $ticket,
                    'ticket_perms'               => $this->_getTicketPerms($ticket),
                    'ticket_messages'            => $ticket_messages,
                    'ticket_messages_translated' => $ticket_messages_translated,
                    'ticket_messages_num'        => $ticket_messages_num,
                    'ticket_message_attachments' => $ticket_message_attachments,
                    'ticket_fwd_logs'            => $ticket_fwd_logs,
                    'ticket_attachments'         => $ticket_attachments,
                    'all_feedback'               => $all_feedback,
                    'message_page'               => $page,
                    'message_count'              => $message_count,
                    'message_page_count'         => $num_pages,
                ]
            );
        }

        $incidents     = 0;
        $problem_id    = 0;
        $problem_title = null;
        if ($this->person->hasPerm('agent_problems.view')) {
            if ($problem = $ticket->getProblems()->first()) {
                $rep            = $this->em->getRepository(Problem::class);
                $problem_counts = $rep->getCountsForAgentInterface([$problem], $this->person);
                $incidents      = (int) @$problem_counts[$problem->id];
                $problem_id     = $problem->id;
                $problem_title  = $problem->title;
            }
        }

        $ticket_messages_blockcache = [
            'status'               => $ticket->getStatusCode(),
            'language_id'          => $ticket->getLanguageId(),
            'urgency'              => $ticket->getUrgency(),
            'department_id'        => $ticket->getDepartmentId(),
            'category_id'          => $ticket->getCategoryId(),
            'product_id'           => $ticket->getProductId(),
            'workflow_id'          => $ticket->getWorkflowId(),
            'priority_id'          => $ticket->getPriorityId(),
            'is_hold'              => $ticket->isHold(),
            'agent_id'             => $ticket->getAgentId(),
            'agent_team_id'        => $ticket->getAgentTeamId(),
            'is_locked'            => $ticket->hasLock(),
            'locked_by_agent_id'   => $ticket->hasLock() ? $ticket->getLockedByAgent()->getId() : null,
            'locked_by_agent_name' => $ticket->hasLock() ? $ticket->getLockedByAgent()->getDisplayName() : null,

            'incidents'     => $incidents,
            'problem_id'    => $problem_id,
            'problem_title' => $problem_title,

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
        ];

        return $ticket_messages_blockcache;
    }

    //###########################################################################
    // ajax-save-flagged
    //###########################################################################

    public function ajaxSaveFlaggedAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);
        $ticket->setFlagForPerson($this->person, $this->in->getString('color'));

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // ajax-save-options
    //###########################################################################

    public function ajaxSaveOptionsAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'edit');

        /** @var TicketChecker $tcheck */
        $tcheck = $this->person->PermissionsManager->TicketChecker;

        if ($this->in->checkIsset('department') && $tcheck->canModify($ticket, 'department')) {
            $ticket['department_id'] = $this->in->getUInt('department');
        }

        if ($tcheck->canModify($ticket, 'fields')) {
            if ($this->in->checkIsset('category')) {
                $ticket['category_id'] = $this->in->getUInt('category');
            }
            if ($this->in->checkIsset('product')) {
                $ticket['product_id'] = $this->in->getUInt('product');
            }
            if ($this->in->checkIsset('priority')) {
                $ticket['priority_id'] = $this->in->getUInt('priority');
            }
        }

        if ($this->in->checkIsset('status')) {
            /** @var TicketStatus $setStatus */
            $status = $this->getContainer()->getTicketStatuses()->findStatusOrException($this->in->checkIsset('status'));
            if ($status->getStatusType() == 'resolved' && !$tcheck->canModify($ticket, 'set_resolved')) {
                $status = null;
            }
            if ($status && $status->getStatusType() == 'awaiting_agent' && !$tcheck->canModify($ticket, 'set_awaiting_agent')) {
                $status = null;
            }
            if ($status && $status->getStatusType() == 'awaiting_user' && !$tcheck->canModify($ticket, 'set_awaiting_user')) {
                $status = null;
            }
            if ($status) {
                $ticket->setTicketStatus($status);
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
                $ticket['agent_id'] = $this->in->getUInt('agent');
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
                $ticket['agent_team_id'] = $this->in->getUInt('agent_team');
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

        return $this->createJsonResponse(
            [
                'success'  => 1,
                'can_view' => $this->person->PermissionsManager->TicketChecker->canView($ticket),
            ]
        );
    }

    //###########################################################################
    // add-participant
    //###########################################################################

    public function addParticipantAction($ticket_id)
    {
        $ticket       = $this->getTicketOr404($ticket_id, 'modify_cc');
        $ticket_perms = $this->_getTicketPerms($ticket);

        $person = null;
        if ($this->in->getUInt('person_id')) {
            $person = $this->em->find(Person::class, $this->in->getUInt('person_id'));
        } elseif ($email_address = $this->in->getString('email_address')) {
            if (!StringEmail::isValueValid($email_address)) {
                return $this->createJsonResponse(
                    [
                        'error'      => true,
                        'error_code' => 'invalid_email',
                    ]
                );
            } elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($email_address)) {
                return $this->createJsonResponse(
                    [
                        'error'      => true,
                        'error_code' => 'invalid_email_gatewayaccount',
                    ]
                );
            }

            $person = $this->em->getRepository(Person::class)->findOneByEmail($email_address);

            if (!$person) {
                $person = new Person();
                $person->setEmail($email_address);
            }
        }

        if (!$person) {
            throw new NotFoundHttpException();
        }

        if ($person->is_agent) {
            return $this->createJsonResponse(
                [
                    'error'      => true,
                    'error_code' => 'is_agent',
                    'cc_list'    => $this->_getTicketCcList($ticket),
                ]
            );
        }

        if ($person->id) {
            if ($ticket->hasParticipantPerson($person) || $ticket->person->getId() == $person->getId()) {
                return $this->createJsonResponse(
                    [
                        'error'      => true,
                        'error_code' => 'is_dupe',
                    ]
                );
            }
        }

        $maxCc = (int) App::getSetting('core_tickets.email_cc_max_count');
        if ($maxCc && $ticket->getCcs()->count() >= $maxCc) {
            return $this->createJsonResponse(
                [
                    'error'      => true,
                    'error_code' => 'cc_limit',
                ]
            );
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

        return $this->createJsonResponse(
            [
                'success' => true,
                'cc_list' => $this->_getTicketCcList($ticket),
            ]
        );
    }

    //###########################################################################
    // remove-participant
    //###########################################################################

    protected function _getTicketCcList($ticket)
    {
        // New reply box
        $participants = $this->em->createQuery(
            '
            SELECT p
            FROM DeskPRO:TicketParticipant p
            LEFT JOIN p.person person
            LEFT JOIN p.person_email person_email
            WHERE p.ticket = ?1
        '
        )->setParameter(1, $ticket)->execute();

        $participant_ids = [];
        $agent_parts     = [];
        $user_parts      = [];

        foreach ($participants as $p) {
            $participant_ids[] = $p->person->id;
            if ($p->person->is_agent) {
                $agent_parts[] = $p;
            } else {
                $user_parts[] = $p;
            }
        }

        $cc_list = $this->renderView(
            'AgentBundle:Ticket:view-user-cc-list.html.twig',
            [
                'user_parts'   => $user_parts,
                'ticket_perms' => $this->_getTicketPerms($ticket),
            ]
        );

        return $cc_list;
    }

    public function removeParticipantAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if (!$this->checkPerm($ticket, 'modify_cc')) {
            return $this->createPermissionErrorResponse('You do not have permission to modify CCs');
        }

        $person = $this->em->find(Person::class, $this->in->getUInt('person_id'));

        if ($person) {
            $part = $this->em->createQuery(
                '
                SELECT part
                FROM DeskPRO:TicketParticipant part
                WHERE part.ticket = ?0 AND part.person = ?1
            '
            )->setParameters([$ticket, $person])->setMaxResults(1)->getOneOrNullResult();

            if (!$part) {
                return $this->createJsonResponse(['success' => false]);
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
        }

        return $this->createJsonResponse(['success' => true, 'cc_list' => $this->_getTicketCcList($ticket)]);
    }

    public function setAgentParticipantsAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_assign_agent');

        $agents = $this->em->getRepository(Person::class)->getPeopleFromIds(
            $this->in->getCleanValueArray('agent_part_ids', 'uint', 'discard')
        );

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

        return $this->createJsonResponse(['sucess' => true, 'cc_list' => $this->_getTicketCcList($ticket)]);
    }

    //###########################################################################
    // ajax-save-labels
    //###########################################################################

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

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // ajax-save-reply
    //###########################################################################

    public function ajaxSaveReplyAction($ticket_id)
    {
        if ($this->in->getBool('reply_is_trans')) {
            $requestMessageOrig  = $this->in->getHtml('message_original');
            $requestMessageTrans = $this->in->getHtml('message');
        } else {
            $requestMessageOrig  = $this->in->getHtml('message');
            $requestMessageTrans = '';
        }

        if (!$requestMessageOrig || $requestMessageOrig == trim($this->person->getPref('agent.ticket_signature'))) {
            return $this->createJsonResponse(['error' => 'no_message']);
        }

        if ($this->in->getBool('options.is_note')) {
            $ticket = $this->getTicketOr404($ticket_id, 'modify_notes');
        } else {
            $ticket = $this->getTicketOr404($ticket_id, 'reply');
        }

        $ticketContext = $this->container->getTicketManager()->createAgentExecutorContext(
            $this->person,
            'newreply',
            'web'
        );
        $ticketContext->getVars()->set('is_via_replybox', true);
        $this->container->getTicketManager()->markAsManaged($ticket);

        $actionType = $this->in->getString('options.action');
        $macroId    = Strings::extractRegexMatch('#macro:(\d+)#', $actionType, 1);
        if ($this->in->getBool('options.is_note')) {
            $macroId    = null;
            $actionType = null;
        }
        if ($macroId) {
            $actionType = 'macro';
        } else {
            if (!$actionType) {
                $actionType = 'awaiting_user';
            }
        }

        $ticketContext->getVars()->set('reply_as_action', $actionType);

        $replyOptions = [];
        if ($this->in->getInt('options.agent_id') != -1 && $this->in->getBool('options.do_assign_agent')) {
            $replyOptions[] = 'agent_assign';
        }
        if ($this->in->getInt('options.agent_team_id') != -1 && $this->in->getBool('options.do_assign_team')) {
            $replyOptions[] = 'agent_team_assign';
        }
        if (!$this->in->getBool('options.notify_user')) {
            $replyOptions[] = 'mute_user_emails';
        }
        $ticketContext->getVars()->set('reply_options', $replyOptions);

        $macro = null;
        if ($macroId) {
            $macro = $this->em->find(TicketMacro::class, $macroId);
        }

        $refreshTab = false;

        $factory    = new ActionsFactory();
        $collection = new ActionsCollection();

        $setStatus = null;
        if ($macro) {
            foreach ($macro->getActions() as $action) {
                $action = $factory->createFromInfo($action);
                if ($action) {
                    if ($action instanceof AgentAction || $action instanceof AgentTeamAction || $action instanceof ReplyAction || $action instanceof ReplySnippetAction) {
                        // Ignore, the replybox itself changed for these actions
                    } else {
                        $refreshTab = true;

                        if ($action instanceof StatusAction) {
                            $setStatus = $action->getFullStatus();
                        }

                        $collection->add($action);
                    }
                }
            }
        } else {
            $setStatus = $actionType;
        }

        // macro fallback status will fail here
        $ticketStatuses = $this->getContainer()->getTicketStatuses();
        /** @var TicketStatus $setStatus */
        $setStatus = $ticketStatuses->findStatusOrException($setStatus);

        if ($setStatus) {
            /** @var TicketChecker $tcheck */
            $tcheck = $this->person->PermissionsManager->TicketChecker;
            switch ($setStatus->getStatusType()) {
                case 'resolved':
                    if (!$tcheck->canModify($ticket, 'set_resolved')) {
                        $setStatus = $ticket->getTicketStatus();
                    }
                    break;
                case 'awaiting_agent':
                    if (!$tcheck->canModify($ticket, 'set_awaiting_agent')) {
                        $setStatus = $ticket->getTicketStatus();
                    }
                    break;
                case 'awaiting_user':
                    if (!$tcheck->canModify($ticket, 'set_awaiting_user')) {
                        $setStatus = $ticket->getTicketStatus();
                    }
                    break;
            }
        }

        //------------------------------
        // Handle new message
        //------------------------------

        $message = new Entity\TicketMessage();
        $message->setTicket($ticket);
        $message->setPerson($this->person);
        $message->setIpAddress($this->getRequest()->getClientIp());
        $message->setCreationSystem(Entity\TicketMessage::CREATED_WEB_AGENT_PORTAL);

        if ($this->in->getBool('is_html_reply')) {
            $messageText = $requestMessageOrig;

            $messageTest = $messageText;
            $messageTest = Strings::trimHtml($messageTest);
            if (!$messageTest || Strings::compareHtml($messageTest, $this->person->getSignatureHtml())) {
                return $this->createJsonResponse(['error' => 'no_message']);
            }

            $message->setMessageHtml($messageText);
            $message->setOriginalMessage($this->in->getRaw('message'));
        } else {
            $message->setMessageText($requestMessageOrig);
        }

        if ($this->in->getBool('options.is_note')) {
            $message['is_agent_note'] = true;
        }

        foreach ($this->in->getCleanValueArray('attach') as $blobId) {
            $blob = $this->em->getRepository(Blob::class)->find($blobId);
            if ($blob) {
                if ($this->em->getRepository(SnippetTranslation::class)->findSnippetBlob($blob)) {
                    $raw_file = $this->get('blob.storage')->copyBlobRecordToString($blob);
                    $blob     = $this->get('blob.storage')->createBlobRecordFromString(
                        $raw_file,
                        $blob->getFilename(),
                        $blob->getContentType(),
                        ['tag' => 'ticket_attachment']
                    );
                }

                $blob->setIsTemp(false);

                $attach = new Entity\TicketAttachment();
                $attach->setBlob($blob);
                $attach->setPerson($this->person);
                $message->addAttachment($attach);
            }
        }

        $blobs = $this->get('attachment_helper')->processInlineBlobs($message->getMessageHtml(), $this->in->getCleanValueArray('blob_inline_ids', 'uint', 'discard'));
        foreach ($blobs as $blob) {
            $attach            = new Entity\TicketAttachment();
            $attach['blob']    = $blob;
            $attach['person']  = $this->person;
            $attach->is_inline = true;
            $message->addAttachment($attach);
        }

        $message->convertEmbeddedImagesToInlineAttach();

        if ($snippetIds = $this->in->getString('options.snippet_ids')) {
            $snippetIds = explode(',', $snippetIds);
            $snippetIds = array_map(
                function ($x) {
                    return (int) trim($x);
                },
                $snippetIds
            );
            $snippetIds = Arrays::removeFalsey($snippetIds);
            $snippetIds = array_unique($snippetIds, SORT_NUMERIC);

            foreach ($snippetIds as $snippetId) {
                if ($this->container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
                    // $snippetId refers here to the SnippetTranslation id
                    $snippetTranslation = $this->em->find(SnippetTranslation::class, $snippetId);

                    if ($snippetTranslation) {
                        $snippetLog = SnippetUseLog::createSnippetTicketLog($message, $this->getPerson(), $snippetTranslation);
                        $snippet    = $snippetLog->getSnippet();
                        $snippet->setUsageCount((int) $snippet->getUsageCount() + 1);
                        $this->em->persist($snippetLog);
                    }
                } else {
                    $snippet = $this->em->find(TextSnippet::class, $snippetId);

                    if ($snippet) {
                        $snippetLog = Entity\TicketObjectUseLog::createSnippetLog($ticket, $this->getPerson(), $snippet);
                        $this->em->persist($snippetLog);
                    }
                }
            }
        }

        if ($macro) {
            $macroLog = Entity\TicketObjectUseLog::createMacroLog($ticket, $this->getPerson(), $macro);
            $this->em->persist($macroLog);
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $messageRepo */
        $messageRepo = $this->em->getRepository(TicketMessage::class);
        if ($dupeMessage = $messageRepo->checkDupeMessage(
            $message,
            $ticket,
            5 * 60
        )
        ) {
            return $this->createJsonResponse(
                [
                    'dupe_message' => true,
                    'message_id'   => $dupeMessage['id'],
                    'time'         => $dupeMessage->date_created->getTimestamp(),
                ]
            );
        } else {
            $ticket->addMessage($message);

            if (!$this->in->getBool('options.notify_user')) {
                $ticketContext->getVars()->set('mute_user_emails', true);
            }
        }

        // havent persisted the messag yet, it was just for dupe checking
        if ((App::getSetting('core_tickets.enable_billing') || App::getSetting(
                    'core_tickets.enable_timelog'
                )) && $this->in->getUInt('charge_time')
        ) {
            $charge = $ticket->addCharge($this->person, $this->in->getUInt('charge_time'));
        } else {
            $charge = false;
        }

        // Translated version
        if ($this->in->getString('reply_is_trans') && $requestMessageTrans) {
            $messageTranslated = new Entity\TicketMessageTranslated();
            $messageTranslated->setTicketMessage($message);
            $messageTranslated->message        = $requestMessageTrans;
            $messageTranslated->from_lang_code = $this->person->getLanguage()->getLocale();
            $messageTranslated->lang_code      = $this->in->getString('reply_is_trans');
            $this->em->persist($messageTranslated);

            $message->primary_translation = $messageTranslated;
        }

        //------------------------------
        // Handle CC'ing/parts
        //------------------------------

        $addParts     = [];
        $newUserIds   = [];
        $remParts     = [];
        $changedParts = false;

        $emailValidator = new StringEmail();

        $delCcEmails = $this->container->getIn()->getCleanValueArray('delcc', 'string', 'discard');
        $delCcEmails = array_map('strtolower', $delCcEmails);

        $addCcEmails = $this->container->getIn()->getCleanValueArray('addcc', 'string', 'discard');
        $addCcEmails = array_map('strtolower', $addCcEmails);

        $addCcEmails = array_filter(
            $addCcEmails,
            function ($v) use ($delCcEmails) {
                return !in_array($v, $delCcEmails);
            }
        );

        if ($addCcEmails) {
            foreach ($addCcEmails as $email) {
                if (!$email || !$emailValidator->isValid($email) || App::$container->getEmailAccountManager(
                    )->findAccountForEmailAddress($email)
                ) {
                    continue;
                }

                $person = $this->em->getRepository(Person::class)->findOneByEmail($email);
                if ($person) {
                    $gotUserIds[] = $person->id;
                } else {
                    $person = Person::newContactPerson(['email' => $email]);
                    $this->em->persist($person);
                    $this->em->flush();
                    $newUserIds[] = $person->id;
                }

                $changedParts = true;
                $addParts[]   = $person;
            }
        }

        if ($delCcEmails) {
            foreach ($delCcEmails as $email) {
                if (!$email || !$emailValidator->isValid($email)) {
                    continue;
                }

                $person = $this->em->getRepository(Person::class)->findOneByEmail($email);

                if ($person) {
                    $changedParts = true;
                    $remParts[]   = $person;
                }
            }
        }

        //------------------------------
        // Save
        //------------------------------

        $this->db->beginTransaction();

        $changedAgent = false;
        $changedTeam  = false;
        try {
            if ((!$message['is_agent_note'] || $macro) && $collection->countActions()) {
                $collection->apply($ticket->getTicketLogger(), $ticket, $this->person);
            }

            if ($addParts) {
                foreach ($addParts as $p) {
                    $ticket->addParticipantPerson($p);
                }
            }
            if ($remParts) {
                foreach ($remParts as $p) {
                    $ticket->removeParticipantPerson($p);
                }
            }

            //------------------------------
            // Handle actions
            //------------------------------

            if ($this->in->getInt('options.agent_id') != -1 && $this->in->getBool('options.do_assign_agent')) {
                $changedAgent       = true;
                $ticket['agent_id'] = $this->in->getUInt('options.agent_id');
            }
            if ($this->in->getInt('options.agent_team_id') != -1 && $this->in->getBool('options.do_assign_team')) {
                $changedTeam             = true;
                $ticket['agent_team_id'] = $this->in->getUInt('options.agent_team_id');
            }

            if (!$message['is_agent_note'] || $macro) {
                if ($actionType != 'macro') {
                    $ticket->setTicketStatus($setStatus);
                }

                if ($this->in->getBool('options.do_kbpending')) {
                    $kbPending = new ArticlePendingCreate();
                    $kbPending->fromArray(
                        [
                            'person'  => $this->person,
                            'ticket'  => $ticket,
                            'message' => $message,
                        ]
                    );
                    $this->em->persist($kbPending);
                }
            }

            $this->container->getTicketManager()->saveTicket($ticket, $ticketContext);

            $this->em->getRepository(Draft::class)->deleteDraft('ticket', $ticket->id);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        if (!$message['is_agent_note'] || $macro) {
            $participants = $this->em->createQuery(
                '
                SELECT p
                FROM DeskPRO:TicketParticipant p
                LEFT JOIN p.person person
                LEFT JOIN p.person_email person_email
                WHERE p.ticket = ?1 AND person.is_agent = TRUE
            '
            )->setParameter(1, $ticket)->execute();

            $updatedAgentPartsCount = count($participants);

            $updatedAgentParts = $this->renderView(
                'AgentBundle:Ticket:view-participants-agents.html.twig',
                [
                    'ticket'       => $ticket,
                    'participants' => $participants,
                ]
            );
        }

        $closeTab = $this->in->getBool('options.close_tab');

        $data = $this->_getMessageBlockInfo(
            $ticket,
            $this->in->getUInt('message_page')
        );

        // New reply box
        $participants = $this->em->createQuery(
            '
            SELECT p
            FROM DeskPRO:TicketParticipant p
            LEFT JOIN p.person person
            LEFT JOIN p.person_email person_email
            WHERE p.ticket = ?1
        '
        )->setParameter(1, $ticket)->execute();

        $participantIds = [];
        $agentParts     = [];
        $userParts      = [];

        foreach ($participants as $p) {
            $participantIds[] = $p->person->id;
            if ($p->person->is_agent) {
                $agentParts[] = $p;
            } else {
                $userParts[] = $p;
            }
        }

        $agents     = $this->em->getRepository(Person::class)->getAgents();
        $agentTeams = $this->em->getRepository(AgentTeam::class)->findAll();

        $replybox = $this->renderView(
            'AgentBundle:Ticket:replybox.html.twig',
            [
                'agents'               => $agents,
                'agent_teams'          => $agentTeams,
                'ticket'               => $ticket,
                'participants'         => $participants,
                'participant_ids'      => $participantIds,
                'agent_parts'          => $agentParts,
                'user_parts'           => $userParts,
                'agent_signature'      => $this->person->getSignature(),
                'agent_signature_html' => $this->person->getSignatureHtml(),
                'ticket_perms'         => $this->_getTicketPerms($ticket),
                'system_account'       => $this->getAccount($ticket),
            ]
        );

        $ccList = $this->renderView(
            'AgentBundle:Ticket:view-user-cc-list.html.twig',
            [
                'user_parts'   => $userParts,
                'ticket_perms' => $this->_getTicketPerms($ticket),
            ]
        );

        if ($charge) {
            $chargeHtml = $this->renderView(
                'AgentBundle:Ticket:view-billing-row.html.twig',
                [
                    'ticket' => $ticket,
                    'charge' => $charge,
                ]
            );
        } else {
            $chargeHtml = false;
        }

        $drafts                = $this->em->getRepository(Draft::class)->getActiveDrafts('ticket', $ticket->getId());
        $data['active_drafts'] = $this->_renderActiveDrafts($ticket, $drafts);

        $errorMessages = [];
        if ($setStatus->getStatusType() == 'resolved') {
            $newticket = new NewTicket($this->em, $this->person);
            $newticket->setValuesFromTicket($ticket);
            $validator = new NewTicketValidator();

            $layout = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
                $ticket->getDepartment()->getId()
            );
            $layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::EDIT_TICKET, $ticket);
            $validator->setLayout($layout);
            if (!$validator->isValid($newticket)) {
                foreach ($validator->getErrorsInfo() as $info) {
                    $errorMessages[] = $info['message'];
                }

                // Need to undo setting status!
                $closeTab       = false;
                $ticket->status = 'awaiting_agent';
                $this->container->getTicketManager()->saveTicket($ticket, $ticketContext);
            }
        }

        $canView = $this->person->PermissionsManager->TicketChecker->canView($ticket);
        if (!$canView) {
            $refreshTab = false;
        }

        $data = array_merge(
            $data,
            [
                'via_reply'                      => true,
                'updated_agent_parts_html'       => isset($updatedAgentParts) ? $updatedAgentParts : '',
                'updated_agent_parts_html_count' => isset($updatedAgentPartsCount) ? $updatedAgentPartsCount : null,
                'replybox_html'                  => $replybox,
                'charge_html'                    => $chargeHtml,
                'changed_agent'                  => $changedAgent,
                'agent_id'                       => $ticket['agent_id'],
                'changed_team'                   => $changedTeam,
                'agent_team_id'                  => $ticket['agent_team_id'],
                'status'                         => $ticket['status'],
                'close_tab'                      => $closeTab,
                'refresh_tab'                    => $refreshTab,
                'client_messages'                => false,
                'cc_list'                        => $ccList,
                'error_messages'                 => $errorMessages ?: false,
                'notified_agents'                => $ticketContext->getVars()->get('notified_agents'),
                'can_view'                       => $canView,
                'api_data'                       => $ticket->toApiData(),

                'message' => $message['message'],
            ]
        );

        return $this->createJsonResponse($data);
    }

    public function updateViewsAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        $data = $this->_getMessageBlockInfo(
            $ticket,
            $this->in->getUInt('message_page')
        );

        // New reply box
        $participants = $this->em->createQuery(
            '
            SELECT p
            FROM DeskPRO:TicketParticipant p
            LEFT JOIN p.person person
            LEFT JOIN p.person_email person_email
            WHERE p.ticket = ?1
        '
        )->setParameter(1, $ticket)->execute();

        $participantIds = [];
        $agentParts     = [];
        $userParts      = [];

        foreach ($participants as $p) {
            $participantIds[] = $p->person->id;
            if ($p->person->is_agent) {
                $agentParts[] = $p;
            } else {
                $userParts[] = $p;
            }
        }

        $agents     = $this->em->getRepository(Person::class)->getAgents();
        $agentTeams = $this->em->getRepository(AgentTeam::class)->findAll();

        $activeDrafts = [$this->em->getRepository(Draft::class)->getDraft('ticket', $ticket->getId())];
        $activeDrafts = $this->get('serializer')->toArray($activeDrafts, new SideloadSerializationContext());

        $replybox = $this->renderView(
            'AgentBundle:Ticket:replybox.html.twig',
            [
                'agents'               => $agents,
                'agent_teams'          => $agentTeams,
                'ticket'               => $ticket,
                'participants'         => $participants,
                'participant_ids'      => $participantIds,
                'agent_parts'          => $agentParts,
                'user_parts'           => $userParts,
                'agent_signature'      => $this->person->getSignature(),
                'agent_signature_html' => $this->person->getSignatureHtml(),
                'ticket_perms'         => $this->_getTicketPerms($ticket),
                'system_account'       => $this->getAccount($ticket),
            ]
        );

        $data = array_merge(
            $data,
            [
                'updated_agent_parts_html'       => 1,
                'updated_agent_parts_html_count' => 1,
                'replybox_html'                  => $replybox,
                'agent_id'                       => $ticket['agent_id'],
                'agent_team_id'                  => $ticket['agent_team_id'],
                'status'                         => $ticket->getStatusCode(),
                'close_tab'                      => false,
                'api_data'                       => $ticket->toApiData(),
                'active_drafts'                  => $activeDrafts,
            ]
        );

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // ajax-get-message-text
    //###########################################################################

    public function ajaxGetMessageTextAction($message_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse(
            [
                'message_id'   => $message->getId(),
                'message_text' => $message->getMessageFullText() ?: $message->getMessageText(),
                'message_html' => $message->getMessageFull() ?: $message->getMessageHtml(),
            ]
        );
    }

    /**
     * @param $message_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function ajaxSaveMessageTextAction($message_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        $oldMessage     = $message->getMessage();
        $oldFullMessage = $message->getMessageFull();

        // Have to get raw text then proc emebedded images, BFORE using HTML cleaner,
        // because the processor uses special classnames which will be stripped using the html_core cleaner
        $newMessage = $this->in->getStringRaw('message_html');
        $newMessage = $message->convertEmbeddedImagesToInlineAttachInText($newMessage);
        $newMessage = $this->cleaner->clean($newMessage, 'html_core');
        $newMessage = Strings::trimHtml($newMessage);
        $newMessage = Strings::prepareWysiwygHtml($newMessage);

        $logOriginalContents =
            $this->in->getBool('log_original_contents')
            || !$this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket);

        $details = [
            'message_id' => $message->getId(),
        ];
        if ($logOriginalContents) {
            $details += [
                'old_message'      => $oldMessage,
                'old_full_message' => $oldFullMessage,
            ];
        }

        $message->setMessageHtml($newMessage);
        $message->message_full = null;

        $ticketLog = new TicketLog();
        $ticketLog
            ->setTicket($ticket)
            ->setPerson($this->person)
            ->setActionType('message_edit')
            ->setIdObject($message->getId())
            ->setDetails($details);

        $this->db->beginTransaction();
        try {
            $this->em->persist($message);
            $this->em->persist($ticketLog);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createJsonResponse(
            [
                'message_id'   => $message->getId(),
                'message_text' => $message->getMessageText(),
                'message_html' => $message->getMessageHtml(),
            ]
        );
    }

    public function ajaxSetNoteAction($message_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        $tm = $this->container->getTicketManager();
        $tm->markAsManaged($ticket);

        $old_val                = $message->is_agent_note;
        $message->is_agent_note = $this->in->getBool('is_note');

        // When converting to a reply, we act as though this is a new
        // agent reply and pass it through newreply triggers
        if ($message->is_agent_note != $old_val) {
            $this->em->persist($message);
            $this->em->flush();

            $ticket->getStateChangeRecorder()->recordData(
                'message_note_status',
                [
                    'message_id'    => $message->id,
                    'is_agent_note' => $message->is_agent_note,
                ]
            );

            $ticket_context = $tm->createAgentExecutorContext($this->person, 'update', 'web');
            $tm->saveTicket($ticket, $ticket_context);

            // fake this as a new message so things like notifications are sent through to the user
            if (!$message->is_agent_note) {
                $ticket->resetStateChangeRecorder();
                $ticket_context = $tm->createAgentExecutorContext($message->person, 'newreply', 'web');
                $ticket->getStateChangeRecorder()->recordData(
                    'free',
                    [
                        'message' => 'This message was converted from a note into a reply by '.$this->person->getDisplayContact(
                            ),
                    ]
                );
                $ticket->getStateChangeRecorder()->record('message', null, $message);
                $tm->saveTicket($ticket, $ticket_context);
            }
        }

        return $this->createJsonResponse(
            [
                'message_id' => $message->getId(),
                'ticket_id'  => $ticket->getId(),
                'is_note'    => $message->is_agent_note,
            ]
        );
    }

    /**
     * @param $message_id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function deleteMessageAction($message_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        if (count($ticket->messages) == 1) {
            $this->db->replace(
                'tickets_deleted',
                [
                    'ticket_id'     => $ticket->getId(),
                    'by_person_id'  => $this->person->getId(),
                    'new_ticket_id' => 0,
                    'reason'        => $this->in->getString('reason'),
                    'date_created'  => date('Y-m-d H:i:s'),
                ]
            );

            $ticket->setStatus('hidden.deleted');

            $hidden_data = $this->_getHiddenBarData($ticket);

            return $this->createJsonResponse(
                [
                    'success'        => true,
                    'ticket_deleted' => true,
                    'hidden_html'    => $this->renderView(
                        'AgentBundle:Ticket:view-hidden-bar.html.twig',
                        [
                            'ticket'           => $ticket,
                            'ticket_perms'     => $this->_getTicketPerms($ticket),
                            'ticket_deleted'   => $hidden_data['ticket_deleted'],
                            'hard_delete_time' => $hidden_data['hard_delete_time'],
                        ]
                    ),
                ]
            );
        } else {
            $m                            = $message;
            $log_data                     = [];
            $log_data['message_id']       = $m->id;
            $log_data['person_id']        = $m->person->id;
            $log_data['person_name']      = $m->person->display_name;
            $log_data['is_agent_note']    = $m->is_agent_note;
            $log_data['is_agent_message'] = $m->person->is_agent;
            $log_data['old_message']      = $m->getMessageHtml();

            foreach ($message->getAttachments() as $attachment) {
                $this->deleteMessageAttachment($attachment);
            }

            $log              = new TicketLog();
            $log->ticket      = $ticket;
            $log->person      = $this->person;
            $log->action_type = 'message_removed';
            $log->id_before   = $m->id;
            $log->details     = $log_data;

            $this->em->persist($log);
            $this->em->remove($message);
            $this->em->flush();

            return $this->createJsonResponse(
                [
                    'success' => true,
                ]
            );
        }
    }

    public function getMessageAttachmentsAction($message_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'AgentBundle:Ticket:message-attachments-overlay.html.twig',
            [
                'ticket'      => $ticket,
                'message'     => $message,
                'attachments' => $message->attachments,
            ]
        );
    }

    /**
     * @param $message_id
     * @param $attachment_id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function deleteMessageAttachmentAction($message_id, $attachment_id)
    {
        /** @var $message \Application\DeskPRO\Entity\TicketMessage */
        $message = $this->em->find(TicketMessage::class, $message_id);
        $ticket  = null;
        if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        $this->container->getTicketManager()->markAsManaged($ticket);

        $attachment = false;
        foreach ($message->attachments as $test_attachment) {
            if ($test_attachment->getId() == $attachment_id) {
                $attachment = $test_attachment;
                break;
            }
        }

        if (!$attachment) {
            throw $this->createNotFoundException();
        }

        if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
            throw new NotFoundHttpException();
        }

        $ticket_log              = new TicketLog();
        $ticket_log->ticket      = $ticket;
        $ticket_log->person      = $this->person;
        $ticket_log->action_type = 'attach_removed';
        $ticket_log->id_object   = $message->getId();
        $ticket_log->id_before   = $attachment->getId();

        $blob                     = $attachment->getBlob();
        $log_data['attach_id']    = $attachment->getId();
        $log_data['blob_id']      = $blob->id;
        $log_data['filename']     = $blob->filename;
        $log_data['filesize']     = $blob->filesize;
        $log_data['content_type'] = $blob->content_type;
        $ticket_log->details      = $log_data;

        $this->em->persist($ticket_log);

        $message->attachments->removeElement($attachment);

        $embed_code = str_replace(
            ':image:',
            ':[^:]*:',
            preg_quote($attachment->getBlob()->getEmbedCode(true, 'image'), '/')
        );
        $message->message = preg_replace("/$embed_code/i", '', $message->message);
        $this->em->persist($message);
        $this->em->flush();

        $this->deleteMessageAttachment($attachment);

        $ticket_attachments         = [];
        $ticket_message_attachments = [];
        foreach ($message->attachments as $message_attach) {
            $ticket_attachments[$message_attach->getId()] = $message_attach;
            $ticket_message_attachments[$message->id][]   = $message_attach->getId();
        }

        return $this->createJsonResponse(
            [
                'success'      => true,
                'message_html' => $this->renderView(
                    'AgentBundle:Ticket:ticket-message.html.twig',
                    [
                        'message'                    => $message,
                        'ticket_message_attachments' => $ticket_message_attachments,
                        'ticket_attachments'         => $ticket_attachments,
                        'ticket'                     => $ticket,
                    ]
                ),
            ]
        );
    }

    /**
     * @param TicketAttachment $attachment
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function deleteMessageAttachment(TicketAttachment $attachment)
    {
        $this->em->remove($attachment);
        $attachment->getMessage()->getAttachments()->removeElement($attachment);
        $this->em->flush();

        $blob           = $attachment->getBlob();
        $originalBlob   = $blob->getOriginalBlob();
        $blobRepository = $this->em->getRepository(Blob::class);
        $blobs          = $blobRepository->findBy(['original_blob' => $originalBlob ?: $blob]);

        if ($originalBlob) {
            array_push($blobs, $originalBlob);
        }
        array_push($blobs, $blob);

        foreach ($blobs as $foundBlob) {
            $this->container->getBlobStorage()->deleteBlobRecord($foundBlob);
        }

        $this->db->delete('tickets_attachments', ['id' => $attachment->getId()]);
    }

    //###########################################################################
    // ajax-save-actions
    //###########################################################################

    public function ajaxSaveActionsAction($ticket_id, Request $request)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify');
        $ticket->getStateChangeRecorder()->touchField('__ajax_save_actions');

        $tm = $this->container->getTicketManager();
        $tm->markAsManaged($ticket);

        $context = $tm->createAgentExecutorContext($this->person, 'update', 'web');

        $old_department_id = $ticket->getDepartmentId();
        $new_department_id = $ticket->getDepartmentId();

        $language = $ticket->language;

        $field_manager        = $this->container->getTicketFieldManager();
        $new_field_manager    = $this->container->getCustomFieldManager();
        $person_field_manager = $this->container->getPersonFieldManager();
        $org_field_manager    = $this->container->getOrgFieldManager();

        $perms_before = $this->_getTicketPerms($ticket);

        $was_hidden  = $ticket->status == 'hidden';
        $was_pending = $ticket->status == 'pending';

        $macro_id = $this->in->getUInt('macro_id');
        if ($macro_id) {
            /** @var $macro Entity\TicketMacro */
            $macro = $this->em->getRepository(TicketMacro::class)->find($macro_id);
            if ($macro) {
                $this->db->beginTransaction();
                try {
                    $macro->performOnTicket($ticket, $this->person);
                    $tm->saveTicket($ticket, $context);
                    $this->db->commit();
                } catch (\Exception $e) {
                    $this->db->rollback();
                    throw $e;
                }
            }
        } else {
            $this->db->beginTransaction();
            try {
                $ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
                $ticket_edit->setPersonContext($this->person);

                // Validate based on department...
                $newticket = new NewTicket($this->em, $this->person);
                $newticket->setValuesFromTicket($ticket);

                foreach (['category_id', 'priority_id', 'product_id', 'workflow_id'] as $f) {
                    if ($this->in->checkIsset("actions.$f")) {
                        $newticket->{$f} = $this->in->getUInt("actions.$f");
                    }
                }
                if (isset($_REQUEST['custom_fields'])) {
                    $newticket->ticket_fields = $_REQUEST['custom_fields'];
                }
                if (isset($_REQUEST['custom_person_fields'])) {
                    $newticket->custom_person_fields = $_REQUEST['custom_person_fields'];
                }
                if (isset($_REQUEST['custom_org_fields'])) {
                    $newticket->custom_org_fields = $_REQUEST['custom_org_fields'];
                }

                if ($this->in->getString('actions.status')) {
                    $ticketStatus = $this->getContainer()->getTicketStatuses()
                        ->findStatusOrException($this->in->getString('actions.status'));
                    if ($ticketStatus->getStatusType() == 'resolved') {
                        $newticket->status = 'resolved';
                    } else {
                        $newticket->status = '';
                    }
                }

                $validator = new NewTicketValidator();
                $layout    = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
                    $newticket->department_id
                );
                $layout = LayoutDisplay::createFromLayout(
                    $layout,
                    LayoutDisplay::EDIT_TICKET,
                    $newticket->getMockTicket()
                );
                $validator->setLayout($layout);

                $actions = $this->in->getCleanValueArray('actions', 'raw', 'raw');

                if (count($actions) == 1 && (isset($actions['department_id']) || isset($actions['urgency']))) {
                    // skip validation for realtime updates
                    if (isset($actions['department_id'])) {
                        // Validation not on dep changes,
                        // because changing dep could change validation options
                        $new_department_id = $actions['department_id'];
                    }
                } elseif ($ticket->status == 'hidden' && count(
                        $actions
                    ) == 2 && isset($actions['status']) && isset($actions['hidden_status'])
                ) {
                    // skip validation just restoring a deleted ticket, validation will apply after
                } else {
                    $params     = $request->request;
                    $noValidate = array_intersect(
                            array_keys($params->get('actions') ?: []),
                            ['agent_team_id', 'agent_id']
                        ) || $params->has('set_agent_part_ids');

                    if (!$noValidate && !$validator->isValid($newticket)) {
                        $free   = [];
                        $fields = [];
                        foreach ($validator->getErrorsInfo() as $info) {
                            $free[]   = htmlspecialchars($info['message']);
                            $fields[] = $info['field'];
                        }

                        return $this->createJsonResponse(
                            ['error' => true, 'error_messages' => $free, 'fields' => $fields]
                        );
                    }
                }

                $result = $ticket_edit->applyActions($actions);

                // If department is changed,
                // then we re-output the holder template
                $is_dep_changed = false;
                $event_listener = new PropertyChangedCallback(
                    function ($sender, $propertyName, $oldValue, $newValue) use (&$is_dep_changed) {
                        if ($propertyName == 'department') {
                            $is_dep_changed = true;
                        }
                    }
                );
                $ticket->addPropertyChangedListener($event_listener);

                if ($this->in->getBool('with_set_agent_parts') && $this->person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
                    $set_parts = $this->in->getCleanValueArray('set_agent_part_ids', 'uint', 'discard');
                    $agents    = $this->em->getRepository(Person::class)->getPeopleFromIds($set_parts);
                    $ticket->setAgentParticipants($agents);
                }

                if ($this->person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
                    if ($this->request->request->has('custom_fields')) {
                        $postCustomFields = $this->request->get('custom_fields', []);
                        $layoutCustomData = $this->filterSubmittedLayoutData($layout, 'ticket_field', $postCustomFields);

                        $field_manager->saveFormToObject($layoutCustomData, $ticket, true);
                        $this->em->persist($ticket);

                        $new_custom_fields = $new_field_manager->createFormForOwner(
                            $ticket,
                            $ticket->person,
                            $layout,
                            ['allow_edit' => true]
                        );
                        if ($org = $ticket->person->organization) {
                            $new_field_manager->merge(
                                $new_custom_fields,
                                $new_field_manager->createFormForOwner(
                                    $ticket,
                                    $org,
                                    $layout,
                                    ['allow_edit' => true]
                                )
                            );
                        }
                        $new_custom_fields->handleRequest($this->request);

                        $this->em->flush();
                    }

                    if ($this->request->request->has('custom_person_fields')) {
                        $postCustomFields = $this->request->get('custom_person_fields') ?: [];
                        $layoutCustomData = $this->filterSubmittedLayoutData($layout, 'user_field', $postCustomFields);

                        $person_field_manager->saveFormToObject($layoutCustomData, $ticket->person, true);
                        $this->em->persist($ticket->person);
                    }

                    if ($this->request->request->has('custom_org_fields') && $ticket->person->organization) {
                        $postCustomFields = $this->request->get('custom_org_fields') ?: [];
                        $layoutCustomData = $this->filterSubmittedLayoutData($layout, 'org_field', $postCustomFields);

                        $org_field_manager->saveFormToObject($layoutCustomData, $ticket->person->organization, true);
                        $this->em->persist($ticket->person->organization);
                    }

                    if ($this->settings->get('core.problems.enabled')) {
                        if (isset($actions['problem_id'])) {
                            $id    = (int) $actions['problem_id'];
                            $title = @$actions['create_problem'];
                            /** @var TicketChecker $checker */
                            $checker = $this->person->PermissionsManager->TicketChecker;

                            switch (true) {
                                case $id > 0 && $checker->canAssociateProblem($ticket):
                                    $problem = $this->em->find(Problem::class, $actions['problem_id']);
                                    $ticket->associateProblem($problem);
                                    break;

                                case 0 === $id && $checker->canDisassociateProblem($ticket):
                                    $ticket->disassociateProblem();
                                    break;

                                case -1 === $id && $this->person->hasPerm('agent_problems.create') && $title:
                                    $problem = new Problem();
                                    $problem->setCreator($this->person)->setTitle($title);
                                    $this->em->persist($problem);
                                    $this->em->flush();
                                    $ticket->associateProblem($problem);
                                    break;
                            }
                        }
                    }
                }

                $tm->saveTicket($ticket, $context);
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        }

        $new_custom_fields = $new_field_manager->createFormForOwner(
            $ticket,
            $ticket->person,
            null,
            ['allow_edit' => true]
        );
        if ($org = $ticket->person->organization) {
            $new_field_manager->merge(
                $new_custom_fields,
                $new_field_manager->createFormForOwner($ticket, $org, null, ['allow_edit' => true])
            );
        }

        $data = ['data' => []];
        if (isset($result['new_reply'])) {
            $data['data']['new_reply'] = $this->renderView(
                'AgentBundle:Ticket:ticket-message.html.twig',
                [
                    'message' => $result['new_reply'],
                ]
            );
        }

        // need to reload the whole ticket if we flipped the language type
        $was_rtl                = ($language && $language->is_rtl);
        $is_rtl                 = ($ticket->language && $ticket->language->is_rtl);
        $data['data']['reload'] = (($was_rtl && !$is_rtl) || (!$was_rtl && $is_rtl));
        $data['holders']        = $this->getDataHolders($ticket);
        $data['labels']         = $ticket->getLabelManager()->getLabelsArray();

        $data['data']['can_view'] = $this->person->PermissionsManager->TicketChecker->canView($ticket);

        $perms_after = $this->_getTicketPerms($ticket);

        if ($perms_before['reply'] != $perms_after['reply']) {
            $data['data']['refresh'] = true;
        }

        if (isset($ticket_edit) && $ticket_edit->getPermErrors()) {
            $data['data']['perm_errors'] = $ticket_edit->getPermErrors();
            $data['data']['refresh']     = true;
        }

        if ($was_hidden && $ticket->status != 'hidden') {
            $data['data']['refresh'] = true;
        }

        // If the department changed and we have new field options,
        // then we'll need to refresh the ticket so those new validation options
        // are enforced
        if (!isset($data['data']['refresh']) && $old_department_id != $ticket->getDepartmentId()) {
            $old_page_ids = [];
            $new_page_ids = [];

            $old_page = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($old_department_id);
            $new_page = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($new_department_id);

            // - We only care about fields that have validation
            // - The actual field show/hide changes are handled in JS on the client
            // - So only when the current validation scheme changes do
            // we need to resort to re-loading the ticket tab
            $fn_check_has_validator = function ($x) use ($field_manager) {
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
                        if (!$field) {
                            return false;
                        }

                        return $field->getOption('agent_required');
                        break;

                    case 'user_field':
                        $field = $field_manager->getFieldFromId($x->getFieldId());
                        if (!$field) {
                            return false;
                        }

                        return $field->getOption('agent_required');
                        break;

                    case 'org_field':
                        $field = $field_manager->getFieldFromId($x->getFieldId());
                        if (!$field) {
                            return false;
                        }

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

            if (count($old_page_ids) != count($new_page_ids) || array_diff($old_page_ids, $new_page_ids) || array_diff(
                    $new_page_ids,
                    $old_page_ids
                )
            ) {
                $data['data']['refresh'] = true;
            }
        }

        $data['data']['api_data'] = $ticket->toApiData();

        if (!$data['data']['can_view']) {
            $data['data']['refresh'] = false;
        }

        return $this->createJsonResponse($data);
    }

    protected function getDataHolders(Entity\Ticket $ticket)
    {
        $field_manager        = $this->container->getTicketFieldManager();
        $person_field_manager = $this->container->getPersonFieldManager();
        $org_field_manager    = $this->container->getOrgFieldManager();
        $new_field_manager    = $this->container->getCustomFieldManager();
        $custom_fields        = $field_manager->getDisplayArrayForObject($ticket);

        $group                = $this->get('form.factory')->createNamedBuilder('custom_person_fields', 'form');
        $custom_person_fields = $person_field_manager->getDisplayArrayForObject($ticket->person, $group);
        $group                = $this->get('form.factory')->createNamedBuilder('custom_org_fields', 'form');
        $custom_org_fields    = $ticket->person->organization
            ? $org_field_manager->getDisplayArrayForObject($ticket->person->organization, $group)
            : [];

        $new_custom_fields = $new_field_manager->createFormForOwner(
            $ticket,
            $ticket->person,
            null,
            ['allow_edit' => true]
        );

        if ($org = $ticket->person->organization) {
            $new_field_manager->merge(
                $new_custom_fields,
                $new_field_manager->createFormForOwner($ticket, $org, null, ['allow_edit' => true])
            );
        }

        $ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

        $open_problems = [];
        $incidents     = 0;
        if ($this->person->hasPerm('agent_problems.view')) {
            $open_problems = $this->em->getRepository(Problem::class)->findBy(
                ['is_open' => true],
                ['title' => 'asc']
            );

            if ($problem = $ticket->getProblems()->first()) {
                $rep            = $this->em->getRepository(Problem::class);
                $problem_counts = $rep->getCountsForAgentInterface([$problem], $this->person);
                $incidents      = (int) @$problem_counts[$problem->id];
            }
        }

        return $this->renderView(
            'AgentBundle:Ticket:view-page-display-holders.html.twig',
            [
                'ticket'               => $ticket,
                'ticket_options'       => $ticket_options,
                'custom_fields'        => $custom_fields,
                'custom_person_fields' => $custom_person_fields,
                'custom_org_fields'    => $custom_org_fields,
                'new_custom_fields'    => $new_custom_fields->createView(),
                'open_problems'        => $open_problems,
                'incidents'            => $incidents,
            ]
        );
    }

    public function getDataHoldersAction($ticket_id)
    {
        $ticket                   = $this->getTicketOr404($ticket_id);
        $data                     = ['data' => []];
        $data['data']['can_view'] = $this->person->PermissionsManager->TicketChecker->canView($ticket);
        $data['holders']          = $this->getDataHolders($ticket);
        $data['labels']           = $ticket->getLabelManager()->getLabelsArray();

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

        return $this->createJsonResponse(['success' => true]);
    }

    public function ajaxChangeUserEmailAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_fields');

        $email_id = $this->in->getUInt('email_id');

        $new_email = $ticket->person->getEmailId($email_id);
        if ($new_email) {
            $ticket->person_email = $new_email;
            $this->em->persist($ticket);
            $this->em->flush();
        }

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // ajax-get-macro-actions
    //###########################################################################

    public function ajaxGetMacroAction($ticket_id)
    {
        if (!$ticket_id) {
            $ticket = new Ticket();
            $ticket->setAgent($this->person);
            if ($personId = $this->in->getUInt('person_id')) {
                $person = $this->em->getRepository(Person::class)->find($personId);
                $ticket->setPerson($person);
            }
        } else {
            $ticket = $this->getTicketOr404($ticket_id);
        }

        $GLOBALS['DP_ACTIVE_TICKET'] = $ticket;

        $macro_id = $this->in->getUInt('macro_id');

        /** @var $macro \Application\DeskPRO\Entity\TicketMacro */
        $macro = $this->em->getRepository(TicketMacro::class)->find($macro_id);

        if (!$macro || (!$macro->is_global && $macro->getPerson() && $macro->getPerson()->getId(
                ) != $this->person->getId())
        ) {
            throw $this->createNotFoundException();
        }

        $descriptions = $macro->getActionDescriptions();

        return $this->createJsonResponse(
            [
                'macro_id'     => $macro->getId(),
                'descriptions' => $descriptions,
            ]
        );
    }

    public function applyMacroAction($ticket_id, $macro_id)
    {
        /** @var $ticket \Application\DeskPRO\Entity\Ticket */
        $ticket = $this->getTicketOr404($ticket_id, 'edit');

        /** @var $macro \Application\DeskPRO\Entity\TicketMacro */
        $macro = $this->em->getRepository(TicketMacro::class)->find($macro_id);

        if (!$macro || (!$macro->is_global && $macro->getPerson() && $macro->getPerson()->getId(
                ) != $this->person->getId())
        ) {
            throw $this->createNotFoundException();
        }

        $actions_collection = $macro->getActionsCollection($ticket);

        $permission_errors = false;
        $this->db->beginTransaction();
        try {
            if (!$actions_collection->applyCheckPermission($ticket, $this->person)) {
                $permission_errors = true;
            } else {
                $reply_action = null;
                if ($actions_collection->hasActionType('Reply')) {
                    $reply_action = $actions_collection->getActionType('Reply');
                    $actions_collection->removeActionType('Reply');
                } elseif ($actions_collection->hasActionType('ReplySnippet')) {
                    $reply_action = $actions_collection->getActionType('ReplySnippet');
                    $actions_collection->removeActionType('ReplySnippet');
                }

                $actions_collection->apply($ticket->getTicketLogger(), $ticket, $this->person);

                $newticket = new NewTicket($this->em, $this->person);
                $newticket->setValuesFromTicket($ticket);
                $newticket->status = $ticket->status;

                $validator = new NewTicketValidator();
                $layout    = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
                    $newticket->department_id
                );
                $layout = LayoutDisplay::createFromLayout(
                    $layout,
                    LayoutDisplay::EDIT_TICKET,
                    $newticket->getMockTicket()
                );
                $validator->setLayout($layout);

                if (!$validator->isValid($newticket)) {
                    $free = [];
                    foreach ($validator->getErrorsInfo() as $info) {
                        $free[] = htmlspecialchars($info['message']);
                    }
                    throw new ValidatorException(implode('|', $free));
                }

                $tm = $this->container->getTicketManager();
                $tm->markAsManaged($ticket);
                $context = $tm->createAgentExecutorContext($this->person, 'update', 'web');
                $tm->saveTicket($ticket, $context);

                if ($reply_action) {
                    $actions_collection = new ActionsCollection();
                    $actions_collection->add($reply_action);

                    $actions_collection->apply($ticket->getTicketLogger(), $ticket, $this->person);

                    $newticket = new NewTicket($this->em, $this->person);
                    $newticket->setValuesFromTicket($ticket);
                    $newticket->status = $ticket->status;

                    $validator = new NewTicketValidator();
                    $layout    = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
                        $newticket->department_id
                    );
                    $layout = LayoutDisplay::createFromLayout(
                        $layout,
                        LayoutDisplay::EDIT_TICKET,
                        $newticket->getMockTicket()
                    );
                    $validator->setLayout($layout);

                    if (!$validator->isValid($newticket)) {
                        $free = [];
                        foreach ($validator->getErrorsInfo() as $info) {
                            $free[] = htmlspecialchars($info['message']);
                        }
                        throw new ValidatorException(implode('|', $free));
                    }

                    $tm = $this->container->getTicketManager();
                    $tm->markAsManaged($ticket);
                    $context = $tm->createAgentExecutorContext($this->person, 'newreply', 'web');
                    $tm->saveTicket($ticket, $context);
                }

                $this->db->commit();
            }
        } catch (\Exception $e) {
            $this->db->rollback();
            if ($e instanceof ValidatorException) {
                return $this->createJsonResponse(['error' => true, 'error_messages' => explode('|', $e->getMessage())]);
            }
        }

        if ($macro) {
            $macroLog = Entity\TicketObjectUseLog::createMacroLog($ticket, $this->getPerson(), $macro);
            $this->em->persist($macroLog);
            $this->em->flush();
        }

        if ($permission_errors) {
            return $this->createJsonResponse(
                [
                    'ticket_id' => $ticket->getId(),
                    'macro_id'  => $macro->getId(),
                    'success'   => false,
                    'error'     => 'permissions',
                ]
            );
        }

        $can_view = $this->person->PermissionsManager->TicketChecker->canView($ticket);

        return $this->createJsonResponse(
            [
                'ticket_id' => $ticket->getId(),
                'macro_id'  => $macro->getId(),
                'close_tab' => (isset($GLOBALS['DP_TICKET_CLOSE_TAB']) && $GLOBALS['DP_TICKET_CLOSE_TAB']) || !$can_view,
                'success'   => true,
            ]
        );
    }

    public function ajaxGetMessageQuoteAction($message_id)
    {
        $message = $this->em->getRepository(TicketMessage::class)->find($message_id);

        $message_quote = wordwrap($message->getMessageText(), 75, "\n", true);
        $message_quote = preg_replace('#^#m', '> ', $message_quote);

        return $this->createJsonResponse(
            [
                'message_id'    => $message['id'],
                'message_quote' => $message_quote,
            ]
        );
    }

    //###########################################################################
    // get-full-message
    //###########################################################################

    public function ajaxGetFullMessageAction($message_id)
    {
        $message = $this->em->find(TicketMessage::class, $message_id);
        if (!$message) {
            throw $this->createNotFoundException();
        }

        $ticket = $this->getTicketOr404($message->ticket->getId());

        $data = [
            'ticket_id'    => $ticket->getId(),
            'message_id'   => $message->getId(),
            'message_full' => $message->getMessageFull(),
        ];

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // save-agent-parts, save-user-parts
    //###########################################################################

    public function saveAgentPartsAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_assign_agent');

        $set_agent_ids = $this->in->getCleanValueArray('person_ids', 'uint', 'discard');
        $ticket->setParticipantAgentIds($set_agent_ids);

        $this->em->transactional(
            function ($em) use ($ticket) {
                $em->persist($ticket);
                $em->flush();
            }
        );

        $participants = $this->em->createQuery(
            '
            SELECT p
            FROM DeskPRO:TicketParticipant p
            LEFT JOIN p.person person
            LEFT JOIN p.person_email person_email
            WHERE p.ticket = ?1
        '
        )->setParameter(1, $ticket)->execute();

        return $this->render(
            'AgentBundle:Ticket:view-participants-agents.html.twig',
            [
                'ticket'       => $ticket,
                'participants' => $participants,
            ]
        );
    }

    public function ticketChargeFormAction($ticket_id)
    {
        $ticket                = $this->getTicketOr404($ticket_id);
        $billing_field_manager = $this->container->getBillingFieldManager();
        $billing_fields_new    = $billing_field_manager->getDisplayArrayForObject(new Entity\TicketCharge());

        return $this->render(
            'AgentBundle:Ticket:view-billing-form.html.twig',
            [
                'billing_fields_new' => $billing_fields_new,
                'ticket'             => $ticket,
                'baseId'             => $this->in->getString('base_id'),
            ]
        );
    }

    //###########################################################################
    // add-charge
    //###########################################################################

    public function addChargeAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if ($this->in->getString('billing_type') == 'amount') {
            $amount = $this->in->getFloat('amount');
            $time   = null;
        } else {
            $amount = null;
            $time   = (
                3600 * $this->in->getUInt('hours')
                + 60 * $this->in->getUInt('minutes')
                + $this->in->getUInt('seconds')
            );
        }

        if (!$charge = $ticket->addCharge($this->person, $time, $amount)) {
            return $this->createJsonResponse(['inserted' => false]);
        }

        $ticketLog = new TicketLog();
        $this->em->persist($charge);
        $this->em->persist($ticketLog);

        $ticketLog->ticket      = $ticket;
        $ticketLog->person      = $this->person;
        $ticketLog->action_type = 'new_billing';
        $ticketLog->id_object   = $charge->getId();
        $ticketLog->details     = [
            'new_amount' => $charge->getAmount(),
            'new_time'   => $charge->getChargeTime(),
        ];

        $fieldManager = $this->container->getBillingFieldManager();
        $customFields = @$_POST['custom_fields'] ?: [];

        $invalidCustomFields = [];
        $isValid             = true;
        $trans               = $this->container->getTranslator();
        foreach ($fieldManager->getFields() as $field) {
            $errors = $field->getHandler()->validateFormData(
                $customFields,
                HandlerAbstract::CONTEXT_AGENT,
                ['exist_ticket' => $ticket]
            );
            foreach ($errors as $code) {
                $code = preg_replace('#.*?\.(.*?)$#', '$1', $code);
                switch ($code) {
                    case 'min_length':
                        $code = 'text_min';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    case 'max_length':
                        $code = 'text_max';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    case 'regex_fail':
                        $code = 'text_regex';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    default:
                        $msg = $trans->getPhraseText('user.error.form_'.$code);
                }
                $invalidCustomFields['field_'.$field->getId()] = $field['title'].': '.$msg;
                $isValid                                       = false;
            }
        }
        if (!$isValid) {
            return $this->createJsonResponse(
                [
                    'inserted'              => false,
                    'invalid_custom_fields' => $invalidCustomFields,
                ]
            );
        }

        $this->em->persist($ticket);

        if (!empty($customFields)) {
            $fieldManager->saveFormToObject($customFields, $charge);
            $changes = $charge->getStateChangeRecorder()->getChanges();

            $class      = 'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator';
            $serialized = sprintf('O:%u:"%s":0:{}', strlen($class), $class);
            $obj        = unserialize($serialized);
            $method     = new \ReflectionMethod(
                'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator',
                'getLogDataForChange'
            );
            $method->setAccessible(true);
            $details = $ticketLog->details;

            foreach ($changes as $change) {
                if (0 !== strpos($change->getField(), 'custom_data.')) {
                    continue;
                }
                $details['custom_data'][$change->getField()] = $method->invoke($obj, $change);
            }
            $ticketLog->details = $details;
        }
        $billingFields[$charge['id']] = $fieldManager->getDisplayArrayForObject($charge);

        // todo need a transaction joined with above
        $details              = $ticketLog->details;
        $details['charge_id'] = $charge->getId();
        $ticketLog->id_object = $charge->getId();
        $ticketLog->details   = $details;
        $this->em->flush();

        return $this->createJsonResponse(
            [
                'inserted' => true,
                'html'     => $this->renderView(
                    'AgentBundle:Ticket:view-billing-row.html.twig',
                    [
                        'ticket_perms'   => $this->_getTicketPerms($ticket),
                        'ticket'         => $ticket,
                        'charge'         => $charge,
                        'billing_fields' => $billingFields,
                    ]
                ),
            ]
        );
    }

    //###########################################################################
    // edit-charge
    //###########################################################################

    public function editChargeAction($ticket_id, $charge_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_billing');

        /** @var Entity\TicketCharge $charge */
        $charge = $this->em->getRepository(TicketCharge::class)->findOneBy(['ticket' => $ticket, 'id' => $charge_id]);

        if (!$charge) {
            return $this->createJsonResponse(
                [
                    'success' => false,
                ]
            );
        }

        $fieldManager = $this->container->getBillingFieldManager();
        $customFields = @$_POST['custom_fields'] ?: [];

        $invalidCustomFields = [];
        $isValid             = true;
        $trans               = $this->container->getTranslator();
        foreach ($fieldManager->getFields() as $field) {
            $errors = $field->getHandler()->validateFormData(
                $customFields,
                HandlerAbstract::CONTEXT_AGENT,
                ['exist_ticket' => $ticket]
            );
            foreach ($errors as $code) {
                $invalidCustomFields['field_'.$field->getId()] = $field['title'].': '.$trans->phrase(
                        preg_replace('#^(.*?)\.#', 'user.error.form_', $code)
                    );
                $isValid = false;
            }
        }
        if (!$isValid) {
            return $this->createJsonResponse(
                [
                    'success'               => false,
                    'invalid_custom_fields' => $invalidCustomFields,
                ]
            );
        }

        $oldAmount = $charge->getAmount();
        $oldTime   = $charge->getChargeTime();

        $amount = $this->in->getFloat('amount');

        $time = (
            3600 * $this->in->getUInt('hours')
            + 60 * $this->in->getUInt('minutes')
            + $this->in->getUInt('seconds')
        );

        if ($charge->getChargeTime()) {
            $charge->charge_time = $time;
        } else {
            $charge->amount = $amount;
        }

        $ticketLog              = new TicketLog();
        $ticketLog->ticket      = $ticket;
        $ticketLog->person      = $this->person;
        $ticketLog->action_type = 'modify_billing';
        $ticketLog->id_object   = $charge->getId();
        $details                = [];

        if ($created = $this->in->getString('date_created')) {
            try {
                $dt = new \DateTime($created, new \DateTimeZone($this->person->getTimezone()));
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $details['old_created'] = $charge->getDateCreated();
                $details['new_created'] = $dt;
                $charge->date_created   = $dt;
            } catch (\Exception $e) {
                return $this->createJsonResponse(
                    [
                        'success' => false,
                    ]
                );
            }
        }

        if ($oldAmount !== $charge->getAmount()) {
            $details['old_amount'] = $oldAmount;
            $details['new_amount'] = $charge->getAmount();
        }

        if ($oldTime !== $charge->getChargeTime()) {
            $details['old_time'] = $oldTime;
            $details['new_time'] = $charge->getChargeTime();
        }

        $fieldManager->saveFormToObject($customFields, $charge);
        if ($changes = $charge->getStateChangeRecorder()->getChanges()) {
            $class      = 'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator';
            $serialized = sprintf('O:%u:"%s":0:{}', strlen($class), $class);
            $obj        = unserialize($serialized);
            $method     = new \ReflectionMethod(
                'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator',
                'getLogDataForChange'
            );
            $method->setAccessible(true);

            foreach ($changes as $change) {
                if (0 !== strpos($change->getField(), 'custom_data.')) {
                    continue;
                }
                $details['custom_data'][$change->getField()] = $method->invoke($obj, $change);
            }
        }

        if ($details) {
            $details['charge_id'] = $charge->getId();
            $ticketLog->details   = $details;
            $this->em->persist($ticketLog);
        }

        $this->em->flush();
        $billingFields[$charge['id']] = $fieldManager->getDisplayArrayForObject($charge);

        return $this->createJsonResponse(
            [
                'updated' => true,
                'html'    => $this->renderView(
                    'AgentBundle:Ticket:view-billing-row.html.twig',
                    [
                        'ticket'         => $ticket,
                        'charge'         => $charge,
                        'billing_fields' => $billingFields,
                        'ticket_perms'   => $this->_getTicketPerms($ticket),
                    ]
                ),
            ]
        );
    }

    public function deleteChargeAction($ticket_id, $charge_id, $security_token)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        $this->ensureAuthToken('delete_charge', $security_token);

        /** @var Entity\TicketCharge $charge */
        $charge = $this->em->getRepository(TicketCharge::class)->findOneBy(['ticket' => $ticket, 'id' => $charge_id]);

        if (!$charge) {
            return $this->createJsonResponse(
                [
                    'success' => false,
                ]
            );
        }

        $ticketLog              = new TicketLog();
        $ticketLog->ticket      = $ticket;
        $ticketLog->person      = $this->person;
        $ticketLog->action_type = 'delete_billing';
        $ticketLog->id_object   = $charge->getId();
        $ticketLog->details     = [
            'charge_id'   => $charge->getId(),
            'old_amount'  => $charge->getAmount(),
            'old_time'    => $charge->getChargeTime(),
            'old_created' => $charge->getDateCreated(),
        ];
        foreach ($charge->getCustomData() as $data) {
            /* @var $data Entity\CustomDataBilling */
            $charge->getStateChangeRecorder()->record('custom_data.'.$data['root_field']['id'], $data, null, true);
        }

        $changes    = $charge->getStateChangeRecorder()->getChanges();
        $class      = 'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator';
        $serialized = sprintf('O:%u:"%s":0:{}', strlen($class), $class);
        $obj        = unserialize($serialized);
        $method     = new \ReflectionMethod(
            'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator',
            'getLogDataForChange'
        );
        $method->setAccessible(true);
        $details = $ticketLog->details;

        foreach ($changes as $change) {
            if (0 !== strpos($change->getField(), 'custom_data.')) {
                continue;
            }
            $details['custom_data'][$change->getField()] = $method->invoke($obj, $change);
        }
        $ticketLog->details = $details;

        $this->em->persist($ticketLog);
        $this->em->remove($charge);
        $this->em->flush();

        return $this->createJsonResponse(
            [
                'success' => true,
            ]
        );
    }

    //###########################################################################
    // add-sla
    //###########################################################################

    public function addSlaAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_slas');
        $tm     = $this->container->getTicketManager();
        $tm->markAsManaged($ticket);

        $sla = $this->em->getRepository(Sla::class)->find($this->in->getUInt('sla_id'));
        if (!$sla || $sla->apply_type != 'manual') {
            throw new NotFoundHttpException();
        }

        if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
            throw new NotFoundHttpException();
        }

        $ticket_sla = $ticket->addSla($sla);
        if ($ticket_sla && !$ticket_sla->id) {
            $this->em->persist($ticket_sla);
            $this->em->flush();

            $context = $tm->createAgentExecutorContext($this->person, 'update', 'web');
            $tm->saveTicket($ticket, $context);

            $data = [
                'inserted' => true,
                'html'     => $this->renderView(
                    'AgentBundle:Ticket:view-sla-row.html.twig',
                    [
                        'ticket'       => $ticket,
                        'ticket_sla'   => $ticket_sla,
                        'ticket_perms' => $this->_getTicketPerms($ticket),
                    ]
                ),
            ];
        } else {
            $data = ['inserted' => false];
        }

        return $this->createJsonResponse($data);
    }

    public function deleteSlaAction($ticket_id, $sla_id, $security_token)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_slas');

        $sla = $this->em->getRepository(Sla::class)->find($sla_id);
        if (!$sla || $sla->apply_type != 'manual') {
            throw new NotFoundHttpException();
        }

        if (!$this->person->PermissionsManager->TicketChecker->canModify($ticket, 'slas')) {
            throw new NotFoundHttpException();
        }

        $this->ensureAuthToken('delete_sla', $security_token);

        $ticket->removeSla($sla);
        $this->em->persist($ticket);
        $this->em->flush();

        $data = [
            'success' => true,
        ];

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // delete
    //###########################################################################

    /**
     * Soft-deletes a ticket.
     *
     * @param $ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'delete');

        if (!$this->person->PermissionsManager->TicketChecker->canDelete($ticket)) {
            throw new NotFoundHttpException();
        }

        $this->db->replace(
            'tickets_deleted',
            [
                'ticket_id'     => $ticket->getId(),
                'by_person_id'  => $this->person->getId(),
                'new_ticket_id' => 0,
                'reason'        => $this->in->getString('reason'),
                'date_created'  => date('Y-m-d H:i:s'),
            ]
        );

        try {
            $this->em->getConnection()->beginTransaction();
            $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->getDeletedStatus());
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        if ($this->in->getBool('ban') && !$ticket->getPerson()->isAgent()) {
            foreach ($ticket->getPerson()->getEmails() as $email) {
                $email_addy = strtolower($email->getEmail());
                App::getDb()->replace(
                    'ban_emails',
                    [
                        'banned_email' => $email_addy,
                        'is_pattern'   => 0,
                    ]
                );
            }

            $person       = $ticket->getPerson();
            $edit_manager = $this->container->getSystemService('person_edit_manager');
            $edit_manager->setPersonContext($this->person);
            $edit_manager->deleteUser($person, $this->container->get('blob.storage'));
        }

        $hidden_data = $this->_getHiddenBarData($ticket);

        return $this->createJsonResponse(
            [
                'success'     => true,
                'banned'      => $this->in->getBool('ban'),
                'hidden_html' => $this->renderView(
                    'AgentBundle:Ticket:view-hidden-bar.html.twig',
                    [
                        'ticket'           => $ticket,
                        'ticket_perms'     => $this->_getTicketPerms($ticket),
                        'ticket_deleted'   => $hidden_data['ticket_deleted'],
                        'hard_delete_time' => $hidden_data['hard_delete_time'],
                    ]
                ),
            ]
        );
    }

    /**
     * Spam a ticket.
     *
     * @param $ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function spamAction($ticket_id)
    {
        $ticket        = $this->getTicketOr404($ticket_id, 'delete');
        $ticket_person = $ticket->person;

        $this->em->getConnection()->beginTransaction();

        try {
            $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->getSpamStatus());
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        if ($this->in->getBool('ban') && !$ticket_person->is_agent) {
            foreach ($ticket->person->emails as $email) {
                $email_addy = strtolower($email->email);
                App::getDb()->replace(
                    'ban_emails',
                    [
                        'banned_email' => $email_addy,
                        'is_pattern'   => 0,
                    ]
                );
            }
        }

        $hidden_data = $this->_getHiddenBarData($ticket);

        return $this->createJsonResponse(
            [
                'success'     => true,
                'hidden_html' => $this->renderView(
                    'AgentBundle:Ticket:view-hidden-bar.html.twig',
                    [
                        'ticket'           => $ticket,
                        'ticket_perms'     => $this->_getTicketPerms($ticket),
                        'ticket_deleted'   => $hidden_data['ticket_deleted'],
                        'hard_delete_time' => $hidden_data['hard_delete_time'],
                    ]
                ),
            ]
        );
    }

    protected function _getHiddenBarData(Ticket $ticket)
    {
        $hard_delete_time = null;
        $ticket_deleted   = false;
        if ($ticket->getTicketStatus()->isDeleted()) {
            $ticket_deleted = $ticket->getDeletionRecord();

            $date_deleted = $ticket['date_created'];
            if ($ticket_deleted['date_created']) {
                $date_deleted = $ticket_deleted['date_created'];
            }

            $hard_delete_time = $date_deleted->getTimestamp() + $this->container->getSetting(
                    'core_tickets.hard_delete_time'
                );
            $hard_delete_time = max(0, $hard_delete_time - time());
        } elseif ($ticket->getTicketStatus()->isSpam()) {
            $hard_delete_time = $ticket->date_status->getTimestamp() + $this->container->getSetting(
                    'core_tickets.spam_delete_time'
                );
            $hard_delete_time = max(0, $hard_delete_time - time());
        }

        return [
            'hard_delete_time' => $hard_delete_time,
            'ticket_deleted'   => $ticket_deleted,
        ];
    }

    //###########################################################################
    // change-user
    //###########################################################################

    public function changeUserOverlayAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_cc');

        return $this->render(
            'AgentBundle:Ticket:change-user-overlay.html.twig',
            [
                'ticket' => $ticket,
            ]
        );
    }

    public function changeUserOverlayPreviewAction($ticket_id, $new_person_id)
    {
        $ticket     = $this->getTicketOr404($ticket_id, 'modify_cc');
        $new_person = $this->em->find(Person::class, $new_person_id);
        if (!$new_person) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            'AgentBundle:Ticket:change-user-overlay-preview.html.twig',
            [
                'ticket'     => $ticket,
                'new_person' => $new_person,
            ]
        );
    }

    public function changeUserAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_cc');

        $old_person = $ticket->person;

        $new_person_id = $this->in->getUInt('new_person_id');
        if ($new_person_id) {
            $new_person = $this->em->find(Person::class, $new_person_id);
            if (!$new_person) {
                throw $this->createNotFoundException();
            }
        } else {
            $name  = $this->in->getString('name');
            $email = $this->in->getString('email');

            if (!$email || !StringEmail::isValueValid($email)) {
                return $this->createJsonResponse(
                    [
                        'success' => false,
                        'error'   => 'Please enter a valid email address',
                    ]
                );
            } elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($email)) {
                return $this->createJsonResponse(
                    [
                        'success' => false,
                        'error'   => 'The email address you entered belongs to a an account in Admin > Tickets > Email Accounts. You cannot set an email account as the ticket user.',
                    ]
                );
            }

            $new_person = $this->em->getRepository(Person::class)->findOneByEmail($email);
            if (!$new_person) {
                $new_person       = new Person();
                $new_person->name = $name;
                $new_person->setEmail($email, true);
            }
        }

        if ($new_person->getId() == $ticket->person->getId()) {
            return $this->createJsonResponse(
                [
                    'success'       => true,
                    'ticket_id'     => $ticket['id'],
                    'old_person_id' => $old_person->getId(),
                    'new_person_id' => $new_person->getId(),
                ]
            );
        }

        if ($new_person->getId()) {
            $part = $this->em->createQuery(
                '
                    SELECT part
                    FROM DeskPRO:TicketParticipant part
                    WHERE part.ticket = ?0 AND part.person = ?1
                '
            )->setParameters([$ticket, $new_person])->setMaxResults(1)->getOneOrNullResult();
            if ($part) {
                $this->em->remove($part);
            }
        }

        $ticket->person       = $new_person;
        $ticket->organization = $new_person->organization;

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

        return $this->createJsonResponse(
            [
                'success'       => true,
                'ticket_id'     => $ticket['id'],
                'old_person_id' => $old_person->getId(),
                'new_person_id' => $new_person->getId(),
            ]
        );
    }

    //###########################################################################
    // merge
    //###########################################################################

    public function mergeOverlayAction($ticket_id, $other_ticket_id = 0)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

        $field_manager = $this->container->getSystemService('ticket_fields_manager');
        $custom_fields = $field_manager->getDisplayArrayForObject($ticket);

        if ($other_ticket_id) {
            $other_ticket        = $this->getTicketOr404($other_ticket_id, 'view');
            $other_custom_fields = $field_manager->getDisplayArrayForObject($other_ticket);
            $can_merge           = $this->person->PermissionsManager->TicketChecker->canMerge($ticket, $other_ticket);
        } else {
            $can_merge           = null;
            $other_ticket        = false;
            $other_custom_fields = false;
        }

        return $this->render(
            'AgentBundle:Ticket:merge-overlay.html.twig',
            [
                'ticket'              => $ticket,
                'custom_fields'       => $custom_fields,
                'other_ticket'        => $other_ticket,
                'other_custom_fields' => $other_custom_fields,
                'can_merge'           => $can_merge,
            ]
        );
    }

    /**
     * Merge a ticket interface.
     *
     * @param $ticket_id
     * @param $other_ticket_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mergeAction($ticket_id, $other_ticket_id)
    {
        $ticket       = $this->getTicketOr404($ticket_id, 'modify_merge');
        $other_ticket = $this->getTicketOr404($other_ticket_id, 'modify_merge');

        $old_ticket_id = $other_ticket['id'];

        try {
            $merge = new TicketMerge($this->person, $ticket, $other_ticket);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('You cannot merge a ticket with itself');
        }

        if ($ticket->getLockedByAgent() && $ticket->getLockedByAgent() !== $this->person && !$this->in->getBool('ticket_force')) {
            return $this->createJsonResponse(
                [
                    'success' => false,
                    'html'    => $this->renderMergeOverlay($ticket_id, $other_ticket_id, 'agent.tickets.error_merge_locked'),
                ],
                400
            );
        }

        if ($other_ticket->getLockedByAgent() && $other_ticket->getLockedByAgent() !== $this->person && !$this->in->getBool('other_ticket_force')) {
            return $this->createJsonResponse(
                [
                    'success' => false,
                    'html'    => $this->renderMergeOverlay($ticket_id, $other_ticket_id, 'agent.tickets.error_merge_locked'),
                ],
                400
            );
        }

        if (!$merge->checkPersonPermission()) {
            throw $this->createNotFoundException('User does not have permission to merge these tickets');
        }

        try {
            $this->em->beginTransaction();
            $merge->merge();
            $this->em->commit();
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('You cannot merge a ticket with itself');
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $this->createJsonResponse(
            [
                'success' => true,
                'id'      => $ticket['id'],
                'old_id'  => $old_ticket_id,
            ]
        );
    }

    private function renderMergeOverlay($ticketId, $otherTicketId, $mergeFailedReason = null)
    {
        $ticket = $this->getTicketOr404($ticketId, 'modify_merge');

        $fieldManager = $this->container->getSystemService('ticket_fields_manager');
        $customFields = $fieldManager->getDisplayArrayForObject($ticket);

        if ($otherTicketId) {
            $otherTicket       = $this->getTicketOr404($otherTicketId, 'view');
            $otherCustomFields = $fieldManager->getDisplayArrayForObject($otherTicket);
            $canMerge          = $this->person->PermissionsManager->TicketChecker->canMerge($ticket, $otherTicket);
        } else {
            $canMerge          = null;
            $otherTicket       = false;
            $otherCustomFields = false;
        }

        return $this->container->get('twig')->render(
            'AgentBundle:Ticket:merge-overlay.html.twig',
            [
                'merge_failed_reason' => $mergeFailedReason,
                'ticket'              => $ticket,
                'custom_fields'       => $customFields,
                'other_ticket'        => $otherTicket,
                'other_custom_fields' => $otherCustomFields,
                'can_merge'           => $canMerge,
            ]
        );
    }

    //###########################################################################
    // split
    //###########################################################################

    public function splitAction($ticket_id, $message_id = 0)
    {
        $ticket = $this->getTicketOr404($ticket_id, 'modify_merge');

        if ($message_id) {
            $message = $this->em->getRepository(TicketMessage::class)->find($message_id);
            if (!$message || $message->ticket->id != $ticket->id) {
                $message = null;
            }
        } else {
            $message = null;
        }

        return $this->render(
            'AgentBundle:Ticket:split-overlay.html.twig',
            [
                'ticket'  => $ticket,
                'message' => $message,
            ]
        );
    }

    public function splitSaveAction($ticket_id)
    {
        $ticket      = $this->getTicketOr404($ticket_id, 'modify_merge');
        $message_ids = $this->in->getCleanValueArray('message_ids', 'uint', 'discard');
        $subject     = $this->in->getString('subject');

        $split = new TicketSplit($ticket);
        $split->setPersonContext($this->person);

        $this->em->beginTransaction();
        try {
            $new_ticket = $split->split($subject, $message_ids);
            $this->em->commit();
        } catch (\InvalidArgumentException $e) {
            if ($e->getCode() == 100) {
                $code = 'no_messages';
            } else {
                $code = 'all_messages';
            }

            return $this->createJsonResponse(
                [
                    'error'      => true,
                    'error_code' => $code,
                    'message'    => $e->getMessage(),
                ]
            );
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $this->em->persist($ticket);
        if ($new_ticket) {
            $this->em->persist($new_ticket);
        }

        $this->em->flush();

        return $this->createJsonResponse(
            [
                'success'            => true,
                'ticket_id'          => $new_ticket ? $new_ticket['id'] : null,
                'old_ticket_deleted' => false,
            ]
        );
    }

    public function forwardOverlayAction($ticket_id, $message_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        $message = $this->em->find(TicketMessage::class, $message_id);
        if (!$message || $message->ticket->getId() != $ticket->getId()) {
            throw $this->createNotFoundException();
        }

        $date_created = clone $message->date_created;
        $date_created->setTimezone($this->person->getDateTimezone());

        $top = trim(
            $this->container->get('templating.email.twig')->render(
                'DeskPRO:emails_common:ticket-fwd-out-header.html.twig',
                [
                    'agent'   => $this->person,
                    'ticket'  => $ticket,
                    'message' => $message,
                ]
            )
        );

        return $this->render(
            'AgentBundle:Ticket:forward-overlay.html.twig',
            [
                'ticket'       => $ticket,
                'message'      => $message,
                'date_created' => $date_created,
                'top'          => $top,
            ]
        );
    }

    public function forwardSendAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        $messagesIds   = $this->in->getCleanValueArray('messages_ids', 'int', 'int');
        $customMessage = Strings::prepareWysiwygHtml(Strings::trimHtml($this->in->getHtml('custom_message')));
        $useMyAddress  = $this->in->getString('from') === 'me';
        $modeInfo      = $this->in->getArrayValue('info');

        switch ($modeInfo['mode']) {
            case 'all':
                $messages = $this->em->getRepository(TicketMessage::class)
                    ->createQueryBuilder('m')
                    ->where('m.ticket = :tid')->setParameter('tid', $ticket->getId())
                    ->andWhere('m.is_agent_note = false')
                    ->orderBy('m.id', 'DESC')
                    ->setMaxResults(60)
                    ->getQuery()
                    ->execute();
                break;
            case 'single':
                $messages = $this->em->getRepository(TicketMessage::class)
                    ->createQueryBuilder('m')
                    ->where('m.ticket = :tid')->setParameter('tid', $ticket->getId())
                    ->andWhere('m.id = :mid')->setParameter('mid', $modeInfo['messageId'])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->execute();
                break;
            case 'from':
                $messages = $this->em->getRepository(TicketMessage::class)
                    ->createQueryBuilder('m')
                    ->where('m.ticket = :tid')->setParameter('tid', $ticket->getId())
                    ->andWhere('m.is_agent_note = false')
                    ->andWhere('m.id <= :mid')->setParameter('mid', $modeInfo['messageId'])
                    ->orderBy('m.id', 'DESC')
                    ->setMaxResults(60)
                    ->getQuery()
                    ->execute();
                break;
            default:
                throw $this->createNotFoundException();
        }

        $all_raw_to   = $this->in->getCleanValueArray('to', 'str', 'str');
        $all_to_types = $this->in->getCleanValueArray('to_type', 'str', 'str');

        $tos  = [];
        $ccs  = [];
        $bccs = [];

        $helpdesk_addresses = [];

        foreach ($all_raw_to as $rowid => $to) {
            $to = trim($to);
            if (!$to) {
                continue;
            }

            $raw_to = \ezcMailTools::parseEmailAddresses($to);
            if (!$raw_to) {
                return $this->createJsonResponse(['error' => 'invalid_address', 'addresses' => [$to]]);
            }

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
                if (!$addr->email) {
                    continue;
                }
                if (!StringEmail::isValueValid($addr->email)) {
                    return $this->createJsonResponse(['error' => 'invalid_address', 'addresses' => [$addr->email]]);
                }
                if ($this->container->getEmailAccountManager()->findAccountForEmailAddress($addr->email)) {
                    $helpdesk_addresses[] = $addr->email;
                } else {
                    $var[$addr->email] = $addr->name;
                }
            }
            unset($var);
        }

        if ($helpdesk_addresses) {
            return $this->createJsonResponse(
                [
                    'error'     => 'to_helpdesk_address',
                    'addresses' => $helpdesk_addresses,
                ]
            );
        }

        if (!$tos && !$ccs && !$bccs) {
            return $this->createJsonResponse(['error' => 'missing_to']);
        }

        $maxEmailSize = App::getSetting('core_email.max_email_size');

        $attachments = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(Blob::class)
            ->findBy(['id' => $this->in->getCleanValueArray('attachments', 'int', 'int')]);
        //we're checking if attachment and messages are fit into max email size;
        $emailSize = 0;

        foreach ($attachments as $blob) {
            /** @var Blob $blob */
            if ((int) $blob->getFilesize() + $emailSize > $maxEmailSize) {
                break; // leave some space for message itself
            }
            $emailSize += (int) $blob->getFilesize();
        }

        $accessCodes = ListUtils::map(
            $ticket->getAccessCodes(),
            function (Entity\TicketAccessCode $tac) {
                return $tac->getAccessCode();
            }
        );
        $accessCodes[] = $ticket->getAccessCode();

        $message = $this->container->getMailer()->createMessage();

        // There shouldnt be any access codes in the body usually,
        // but it's possible they might be in there because of a badly
        // cut reply back to the helpdesk. So this filter removes them.
        $message->setBodyFilter(function ($body) use ($accessCodes) {
            return str_replace($accessCodes, '', $body);
        });

        foreach ($tos as $k => $x) {
            $message->addTo($k, $x);
        }
        foreach ($ccs as $k => $x) {
            $message->addCc($k, $x);
        }
        foreach ($bccs as $k => $x) {
            $message->addBcc($k, $x);
        }

        $account = $this->getAccount($ticket);

        $useMyAddress = $useMyAddress && $this->container->getSetting('core_tickets.fwd_use_agent_address');
        if ($useMyAddress) {
            $fromEmail = $this->person->getEmailAddress();
        } else {
            $fromEmail = $account->getUseEmailAddress();
        }

        $fromName = $this->person->getDisplayNameUser();

        try {
            $message->setFrom($fromEmail, $fromName);
        } catch (\Swift_RfcComplianceException $e) {
            SystemErrorHandler::logException($e, false);
            throw $this->createNotFoundException();
        }

        $tr = $this->container->getEmailAccountManager()->getTransportForAccount($account);

        if ($message instanceof MessageOptionsInterface) {
            if ($tr) {
                $message->getMessageOptions()->set(MessageOptionsInterface::OPT_ACCOUNT_ID, $account->id);
            }
            if ($useMyAddress) {
                $message->getMessageOptions()->set(MessageOptionsInterface::OPT_USE_FROM, $fromEmail);
            }
        }

        $max  = App::getSetting('core.sendemail_attach_maxsize');
        $size = 0;

        // now process inline attachments
        $ticketDisplay  = new TicketDisplay($ticket, $this->person);
        $allAttachments = $ticketDisplay->getAttachments();
        foreach ($allAttachments as $attachment) {
            if (
                $attachment->isInline()
                && in_array($attachment->getMessage(), $messages, true)
            ) {
                if ((int) $attachment->getBlob()->getFilesize() + $size > $max) {
                    break;
                }
                $message->attachBlob($attachment->getBlob(), $attachment->getBlob()->getDownloadUrl(true), true);
                $size += (int) $attachment->getBlob()->getFilesize();
            }
        }

        // and now attachments
        foreach ($attachments as $blob) {
            /** @var Blob $blob */
            if ($size + (int) $blob->filesize > $max) {
                break;
            }
            $size += (int) $blob->filesize;
            $message->attachBlob($blob, $blob->getDownloadUrl(true), false);
        }

        if ($this->container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->container->get('email.agent_viewmodel_factory')
                ->createAgentTicketForwardModel(
                    $ticket,
                    $customMessage,
                    $this->in->getString('subject')
                );

            $message = $this->getContainer()->get('email.email_sender')
                ->prepareMessage($viewModel, [], $message);
        } else {
            $message->setTemplate(
                'DeskPRO:emails_user:ticket-fwd.html.twig',
                [
                    'ticket'        => $ticket,
                    'subject'       => $this->in->getString('subject'),
                    'messages'      => $messages,
                    'person'        => $this->getPerson(),
                    'agent_message' => $customMessage,
                ]
            );
        }
        $this->container->getMailer()->send($message);

        foreach ($messagesIds as $messageId) {
            // Log the action
            $this->db->insert(
                'tickets_logs',
                [
                    'ticket_id'   => $ticket->id,
                    'person_id'   => $this->person->id,
                    'action_type' => 'message_forwarded',
                    'details'     => serialize(
                        [
                            'message_id'     => $messageId,
                            'agent_id'       => $this->person->id,
                            'agent_name'     => $this->person->getDisplayName(),
                            'to'             => array_keys($tos),
                            'cc'             => array_keys($ccs),
                            'bcc'            => array_keys($bccs),
                            'all_rec_string' => implode(
                                ', ',
                                array_merge(array_keys($tos), array_keys($ccs), array_keys($bccs))
                            ),
                            'to_string'      => implode(', ', array_keys($tos)),
                            'cc_string'      => implode(', ', array_keys($ccs)),
                            'bcc_string'     => implode(', ', array_keys($bccs)),
                            'from_email'     => $fromEmail,
                            'from_name'      => $fromName,
                            'custom_message' => $customMessage ?: null,
                        ]
                    ),
                    'date_created' => date('Y-m-d H:i:s'),
                ]
            );
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function forwardSendLegacyAction($ticket_id, $message_id)
    {
        $ticket  = $this->getTicketOr404($ticket_id);
        $message = $this->em->find(TicketMessage::class, $message_id);
        if (!$message || $message->ticket->getId() != $ticket->getId()) {
            throw $this->createNotFoundException();
        }
        $custom_message     = $this->in->getString('custom_message');
        $all_raw_to         = $this->in->getCleanValueArray('to', 'str', 'str');
        $all_to_types       = $this->in->getCleanValueArray('to_type', 'str', 'str');
        $tos                = [];
        $ccs                = [];
        $bccs               = [];
        $helpdesk_addresses = [];
        foreach ($all_raw_to as $rowid => $to) {
            $to = trim($to);
            if (!$to) {
                continue;
            }
            $raw_to = \ezcMailTools::parseEmailAddresses($to);
            if (!$raw_to) {
                continue;
            }
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
            return $this->createJsonResponse(
                [
                    'error'     => 'to_helpdesk_address',
                    'addresses' => $helpdesk_addresses,
                ]
            );
        }
        if (!$tos) {
            return $this->createJsonResponse(['error' => 'invalid_to']);
        }
        $subject     = $this->in->getString('subject');
        $message_raw = $message->getMessageFull();
        if (!$message_raw) {
            $message_raw = $message->getMessageHtml();
        }
        $date_created = clone $message->date_created;
        $date_created->setTimezone($this->person->getDateTimezone());
        $date_created = $date_created->format($this->container->getSetting('core.date_fulltime'));
        $top          = trim(
            $this->container->get('templating.email.twig')->render(
                'DeskPRO:emails_common:ticket-fwd-out-header.html.twig',
                [
                    'agent'   => $this->person,
                    'ticket'  => $ticket,
                    'message' => $message,
                ]
            )
        );
        if ($custom_message) {
            if ($top) {
                $top .= '<br/><br/>';
            }
            $top .= '<div style="font-family: \'Helvetica Neue\',​Helvetica,​Arial,​sans-serif; font-size: 13px; color: #404040; padding: 0; margin: 0;">';
            $top .= nl2br(htmlspecialchars($custom_message));
            if ($sig = $this->person->getSignatureHtml()) {
                $top .= '<br/><br/>'.$sig.'<br/><br/><br/>';
            }
            $top .= '</div>';
        }
        if ($top) {
            $top .= '<br/><br/>';
        }
        $top .= '<div style="font-family: \'Helvetica Neue\',​Helvetica,​Arial,​sans-serif; font-size: 13px; color: #404040; padding: 0; margin: 0;">';
        $top .= '--- Forwarded Message ---<br/>';
        $top .= 'From: '.$message->getPerson()->getDisplayNameUser().' &lt;<a href="mailto:'.$message->getPerson()
                ->getPrimaryEmailAddress().'">'.$message->getPerson()->getPrimaryEmailAddress().'</a>&gt;<br/>';
        if ($message->getPerson()->isAgent()) {
            $to = $ticket->getPerson();
            $top .= 'To: '.$to->getDisplayName().' &lt;<a href="mailto:'.$to->getPrimaryEmailAddress(
                ).'">'.$to->getPrimaryEmailAddress().'</a>&gt;<br/>';
        } else {
            if ($ticket->getEmailAccount()) {
                $to = $ticket->getEmailAccount();
                $top .= 'To: &lt;<a href="mailto:'.$to['address'].'">'.$to['address'].'</a>&gt;<br/>';
            }
        }
        $top .= 'Subject: '.htmlspecialchars($ticket->getSubject()).'<br/>';
        $top .= 'Date: '.$date_created.'<br/>';
        $top .= '</div>';
        $message_raw = $top.'<br/><br/>'.$message_raw;
        if (strpos($message_raw, '<body') === false) {
            $message_raw = '<html><head><style>body { font-size: 13px; color: #404040; font-family: "Helvetica Neue",​Helvetica,​Arial,​sans-serif; }</style></head><body>'.$message_raw.'</body></html>';
        }
        $message_raw = $message->procInlineAttach($message_raw);
        $email       = $this->container->getMailer()->createMessage();
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
                $account = $this->container->getEmailAccountManager()->getAccount(
                    $this->container->getSetting('core_tickets.fwd_use_account')
                );
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
            $use_from   = true;
            $from_email = $this->person->getEmailAddress();
        } else {
            $use_from   = false;
            $from_email = $account->getUseEmailAddress();
        }
        $from_name = $this->person->getDisplayNameUser();
        try {
            $email->setFrom($from_email, $from_name);
        } catch (\Swift_RfcComplianceException $e) {
            SystemErrorHandler::logException($e, false);
            throw $this->createNotFoundException();
        }
        $tr = $this->container->getEmailAccountManager()->getTransportForAccount($account);
        if ($email instanceof MessageOptionsInterface) {
            if ($tr) {
                $email->getMessageOptions()->set(MessageOptionsInterface::OPT_ACCOUNT_ID, $account->id);
            }
            if ($use_from) {
                $email->getMessageOptions()->set(MessageOptionsInterface::OPT_USE_FROM, $from_email);
            }
        }
        $ticketdisplay      = new TicketDisplay($ticket, $this->person);
        $attach_attachments = [];
        $max                = App::getSetting('core.sendemail_attach_maxsize');
        $size               = 0;
        $attachments        = $ticketdisplay->getMessageAttachments($message, true);
        if ($attachments) {
            foreach ($attachments as $attach) {
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
        $this->db->insert(
            'tickets_logs',
            [
                'ticket_id'   => $ticket->id,
                'person_id'   => $this->person->id,
                'action_type' => 'message_forwarded',
                'details'     => serialize(
                    [
                        'message_id'     => $message_id,
                        'agent_id'       => $this->person->id,
                        'agent_name'     => $this->person->getDisplayName(),
                        'to'             => array_keys($tos),
                        'cc'             => array_keys($ccs),
                        'bcc'            => array_keys($bccs),
                        'all_rec_string' => implode(
                            ', ',
                            array_merge(array_keys($tos), array_keys($ccs), array_keys($bccs))
                        ),
                        'to_string'      => implode(', ', array_keys($tos)),
                        'cc_string'      => implode(', ', array_keys($ccs)),
                        'bcc_string'     => implode(', ', array_keys($bccs)),
                        'from_email'     => $from_email,
                        'from_name'      => $from_name,
                        'custom_message' => $custom_message ?: null,
                    ]
                ),
                'date_created' => date('Y-m-d H:i:s'),
            ]
        );

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // view-raw-message
    //###########################################################################

    public function viewRawMessageAction($ticket_id, $message_id)
    {
        $message = $this->em->find(TicketMessage::class, $message_id);

        $messageRaw = $message->message_raw ?: '';
        if (!$messageRaw) {
            $messageRaw = $message->message_full;
            if (!$messageRaw) {
                $messageRaw = $message->message;
            }
        }

        require_once DP_ROOT.'/vendor-src/htmlpurifier/HTMLPurifier.standalone.php';

        if ($this->in->getBool('raw')) {
            $this->ensureAuthToken('view_raw', $this->in->getString('raw'));
        } else {
            $note = '<div style="font-family: sans-serif; font-size: 11px;border-bottom: 1px solid #C5C5C5; margin-bottom: 3px; padding-bottom: 3px;">This is a safe version of the raw HTML message. <a href="'.$this->generateUrl(
                    'agent_ticket_message_raw',
                    [
                        'ticket_id'  => $ticket_id,
                        'message_id' => $message_id,
                        'raw'        => App::getSession()->generateSecurityToken('view_raw'),
                    ]
                ).'">Click here to view the original message with no modifications</a>. Note that a malicious user may have injected harmful HTML into the message and viewing the original message may result in harmful code being executed.</div>';

            $purifier = new \HTMLPurifier();
            $config   = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('HTML.TidyLevel', 'none');
            // Everything but script/iframe/applet/object
            $config->set(
                'HTML.Allowed',
                'a,abbr,acronym,address,area,b,base,basefont,bdo,big,blockquote,body,br,button,caption,center,cite,code,col,colgroup,dd,del,dfn,dir,div,dl,dt,em,fieldset,font,form,frame,frameset,h1,2,h3,h4,h5,h6,head,hr,html,i,img,input,ins,kbd,label,legend,li,link,map,menu,meta,noframes,noscript,ol,optgroup,option,p,pre,q,s,samp,select,small,span,strike,strong,style,su,sup,table,tbody,td,textarea,tfoot,th,thead,title,tr,tt,u,ul,var'
            );
            $config->set(
                'HTML.AllowedAttributes',
                'class,id,alt,title,align,border,width,height,valign,style,cellspacing,cellpadding,colspan,rowspan,bgcolor,dir,href,target,name,rel,size,type,value,src'
            );
            $config->set('URI.DisableExternalResources', true);

            $messageRaw = $note.$purifier->purify($messageRaw, $config);
        }

        if (strpos($messageRaw, '<body') === false) {
            $messageRaw = '<html><head><style>body { font-size: 13px; color: #404040; font-family: "Helvetica Neue",​Helvetica,​Arial,​sans-serif; }</style><script type="text/javascript">document.domain = document.domain;</script></head><body>'.$messageRaw.'</body></html>';
        }

        $messageRaw = $message->procInlineAttach($messageRaw);

        $res = new Response($messageRaw);

        return $res;
    }

    public function viewMessageWindowAction($message_id, $type = 'normal')
    {
        $message = $this->em->getRepository(TicketMessage::class)->find($message_id);

        if (!$message) {
            throw $this->createNotFoundException();
        }

        $ticket = $message->ticket;

        $vars = [
            'message' => $message,
            'ticket'  => $ticket,
            'type'    => $type,
        ];

        if (!$messageRaw = $message->message_raw ?: '') {
            $messageRaw = $message->message_full ?: $message->message;
        }

        switch ($type) {
            case 'raw':
                require_once DP_ROOT.'/vendor-src/htmlpurifier/HTMLPurifier.standalone.php';
                $purifier = new \HTMLPurifier();
                $config   = \HTMLPurifier_Config::createDefault();
                $config->set('Cache.DefinitionImpl', null);
                $config->set('Core.Encoding', 'UTF-8');
                $config->set('HTML.TidyLevel', 'none');
                // Everything but script/iframe/applet/object
                $config->set(
                    'HTML.Allowed',
                    'a,abbr,acronym,address,area,b,base,basefont,bdo,big,blockquote,body,br,button,caption,center,cite,code,col,colgroup,dd,del,dfn,dir,div,dl,dt,em,fieldset,font,form,frame,frameset,h1,2,h3,h4,h5,h6,head,hr,html,i,img,input,ins,kbd,label,legend,li,link,map,menu,meta,noframes,noscript,ol,optgroup,option,p,pre,q,s,samp,select,small,span,strike,strong,style,su,sup,table,tbody,td,textarea,tfoot,th,thead,title,tr,tt,u,ul,var'
                );
                $config->set(
                    'HTML.AllowedAttributes',
                    'class,id,alt,title,align,border,width,height,valign,style,cellspacing,cellpadding,colspan,rowspan,bgcolor,dir,href,target,name,rel,size,type,value,src'
                );
                $config->set('URI.DisableExternalResources', true);
                $messageRaw = $purifier->purify($messageRaw, $config);
                break;

            case 'email_source':

                $message = $this->em->getRepository(TicketMessage::class)->find($message_id);

                if ($message->email_source) {
                    $r = $this->getContainer()->getEmailEzcReaderFactory()->create();
                    $r->setRawSource($message->email_source->raw_source);
                    $body_html = $r->getBodyHtml() ? $r->getBodyHtml()->getBodyUtf8() : null;
                    $body_text = $r->getBodyText() ? $r->getBodyText()->getBodyUtf8() : null;

                    $vars['raw_source'] = $message->email_source->raw_source;
                    $vars['body_html']  = $body_html;
                    $vars['body_text']  = $body_text;

                    unset($r);
                    $message->email_source->clearRawSource();
                }

                break;
        }

        $vars['message_raw'] = $messageRaw;

        return $this->render('AgentBundle:Ticket:ticket-message-window.html.twig', $vars);
    }

    //###########################################################################
    // new
    //###########################################################################

    public function newAction()
    {
        $ticketOptions = App::getApi('tickets')->getTicketOptions($this->person);

        /** @var \Application\DeskPRO\EntityRepository\Person $personRep */
        $personRep  = $this->em->getRepository(Person::class);
        $agents     = $personRep->getAgents();
        $agentTeams = $this->em->getRepository(AgentTeam::class)->findAll();

        $brands = $this->getAgentBrands();

        //------------------------------
        // Custom fields
        //------------------------------

        if ($this->in->getUInt('ticket_id')) {
            $ticket = $this->getTicketOr404($this->in->getUInt('ticket_id'));

            $message = null;
            /** @var \Application\DeskPRO\EntityRepository\TicketMessage $ticketMessageRepo */
            $ticketMessageRepo = $this->em->getRepository(TicketMessage::class);
            if ($this->in->getUInt('message_id')) {
                $message = $ticketMessageRepo->find($this->in->getUInt('message_id'));
            }
            if (!$message || $message->ticket != $ticket) {
                $message = $ticketMessageRepo->getFirstTicketMessage($ticket);
            }
        } else {
            $ticket  = new Ticket();
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

        $departments = App::$container->get('form_hierarchy_generator')->generateTicketDepartmentsHierarchy($this->person);
        if ($departments->countSelectable() === 1) {
            $ticket->setDepartment($departments->getFirstSelectable());
        }

        if ($message && count($message->attachments)) {
            $attachments = [];
            $storage     = $this->container->getBlobStorage();

            foreach ($message->attachments as $attach) {
                try {
                    $newBlob = $storage->createBlobRecordFromString(
                        $storage->copyBlobRecordToString($attach->blob),
                        $attach->blob['filename'],
                        $attach->blob['content_type']
                    );
                } catch (\Exception $ex) {
                    // $ex should be looged internally in services
                    // no need to additional log here
                    continue;
                }
                $this->em->persist($newBlob);

                $attachData         = [];
                $attachData['blob'] = $newBlob->toArray();
                $attachData['url']  = $newBlob->getDownloadUrl(true);
                $attachments[]      = $attachData;
            }
        }

        $fieldManager = $this->container->getTicketFieldManager();
        $customFields = $fieldManager->getDisplayArrayForObject($ticket);

        $billingFieldManager = $this->container->getBillingFieldManager();
        $group               = $this->container->get('form.factory')->createNamedBuilder('billing_fields');
        $billingFields       = $billingFieldManager->getDisplayArrayForObject(new Entity\TicketCharge(), $group);

        $pid                    = (int) $this->request->get('person_id');
        $person                 = $pid ? $personRep->find($pid) : new Person();
        $customPersonFieldsForm = $this->get('form.factory')->createNamedBuilder('custom_person_fields', 'form');
        $customOrgFieldsForm    = $this->get('form.factory')->createNamedBuilder('custom_org_fields', 'form');
        $customPersonFields     = $this->container->getPersonFieldManager()->getDisplayArrayForObject(
            $person,
            $customPersonFieldsForm
        );
        $customOrgFields = $person->organization
            ? $this->container->getOrgFieldManager()->getDisplayArrayForObject(
                $person->organization,
                $customOrgFieldsForm
            )
            : [];

        $layouts = $this->container->getTicketLayoutManager()->getAgentLayouts();
        $page    = $layouts->getLayout($ticket->department ? $ticket->department['id'] : 0);
        $layout  = LayoutDisplay::createFromLayout($page, LayoutDisplay::NEW_TICKET);

        $manager         = $this->container->getCustomFieldManager();
        $newCustomFields = $manager->createFormForOwner($ticket, $ticket->person, $layout);
        if ($ticket->person && ($org = $ticket->person->organization)) {
            $manager->merge($newCustomFields, $manager->createFormForOwner($ticket, $org, $layout));
        }

        $openProblems = $this->em->getRepository(Problem::class)->findBy(
            ['is_open' => true],
            ['title' => 'asc']
        );

        $defaultDepartments = [];
        foreach ($brands as $brand) {
            $defaultDepartment                   = $this->container->get('brand_form_helper')->getDefaultDepartment(DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE, $brand);
            $defaultDepartments[$brand->getId()] = $defaultDepartment ? $defaultDepartment->getId() : null;
        }

        return $this->render(
            'AgentBundle:Ticket:newticket.html.twig',
            [
                'ticket'               => $ticket,
                'person'               => $person,
                'message'              => $message,
                'attachments'          => isset($attachments) ? $attachments : null,
                'agents'               => $agents,
                'agent_signature'      => $this->person->getSignature(),
                'agent_signature_html' => $this->person->getSignatureHtml(),
                'agent_teams'          => $agentTeams,
                'ticket_options'       => $ticketOptions,
                'custom_fields'        => $customFields,
                'new_custom_fields'    => $newCustomFields->createView(),
                'billing_fields'       => $billingFields,
                'open_problems'        => $openProblems,
                'custom_person_fields' => $customPersonFields,
                'custom_org_fields'    => $customOrgFields,
                'brands'               => $brands,
                'default_brand'        => $this->get('brand_stack')->getDefaultBrand()->getId(),
                'default_departments'  => $defaultDepartments,
                'ticket_statuses'      => App::getContainer()->getTicketStatuses()->getTopLevelStatuses(true),
            ]
        );
    }

    public function newSaveAction(Request $request)
    {
        if (!$this->person->hasPerm('agent_tickets.create')) {
            throw new NotFoundHttpException();
        }

        $newTicket = new NewTicket(
            $this->em,
            $this->person
        );
        $newTicket->setBlobInlineIds($this->in->getCleanValueArray('blob_inline_ids', 'uint', 'discard'));

        if (!$this->in->getBool('options.notify_user')) {
            $newTicket->suppress_user_notify = true;
        }

        $formType = new \Application\AgentBundle\Form\Type\NewTicket();
        $form     = $this->get('form.factory')->create($formType, $newTicket);

        if ($request->getMethod() === 'POST') {
            if ($request->get('is_note')) {
                $newTicket->is_note = true;
            }

            $action_type = $this->in->getString('options.action');
            $macro_id    = Strings::extractRegexMatch('#macro:(\d+)#', $action_type, 1);
            if ($macro_id) {
                $action_type = 'macro';
            }

            $macro = null;
            if ($macro_id) {
                $macro = $this->em->find(TicketMacro::class, $macro_id);
            }

            $factory    = new ActionsFactory();
            $collection = new ActionsCollection();
            $set_status = 'awaiting_agent';

            if ($action_type != 'macro') {
                $set_status = $action_type;
            }

            if ($macro) {
                foreach ($macro->getActions() as $action) {
                    $action = $factory->createFromInfo($action);
                    if ($action) {
                        if ($action instanceof StatusAction) {
                            $set_status = $action->getFullStatus();
                        } elseif ($action instanceof AgentAction || $action instanceof AgentTeamAction || $action instanceof ReplyAction || $action instanceof ReplySnippetAction) {
                            // Ignore, the replybox itself changed for these actions
                        } else {
                            $collection->add($action);
                        }
                    }
                }
            }

            $form->handleRequest($request);
            $form->isValid();

            //------------------------------
            // Validate
            //------------------------------

            $errors = [];

            // Person
            $person_id = $this->in->getUInt('newticket.person.id');
            if ($person_id) {
                $check_person = $this->em->find(Person::class, $person_id);
                if (!$check_person) {
                    $errors['person_id'] = true;
                }

                if ($check_person->is_disabled) {
                    $errors['person_disabled'] = true;
                }
            } else {
                $new_email = $this->in->getString('newticket.person.email_address');
                if (!$new_email) {
                    $new_email                        = $this->in->getString('newticket.person_input_choice');
                    $newTicket->person->email_address = $new_email;
                }

                if (!$new_email && !$this->in->getString('newticket.person.name')) {
                    $errors['person_no_user'] = true;
                } elseif (!StringEmail::isValueValid($new_email)) {
                    $errors['person_email_address'] = true;
                } elseif (App::$container->getEmailAccountManager()->findAccountForEmailAddress($new_email)) {
                    $errors['person_email_address_gateway'] = true;
                }

                $check_person = $this->em->getRepository(Person::class)->findOneByEmail($new_email);
                if ($check_person && $check_person->is_disabled) {
                    $errors['person_disabled'] = true;
                }
            }

            if (!$newTicket->subject) {
                $errors['subject'] = true;
            }
            if (!$newTicket->message) {
                $errors['message'] = true;
            }
            if (!$newTicket->brand_id) {
                $brands = $this->getAgentBrands();
                if (count($brands) === 1) {
                    $brand               = ListUtils::first($brands);
                    $newTicket->brand_id = $brand->getId();
                } else {
                    $errors['brand_id'] = true;
                }
            }
            if (!$this->in->getString('newticket.department_id')) {
                $errors['department_id'] = true;
            }

            $maxCc = (int) App::getSetting('core_tickets.email_cc_max_count');
            if ($maxCc) {
                $ccAddPersons = array_unique($this->in->getCleanValueArray('newticket.add_cc_person', 'uint'));
                //@TODO remove duplicates
                $ccAddNewPersons = $this->in->getCleanValueArray(
                    'newticket.add_cc_newperson',
                    'raw',
                    'discard'
                );
                $cnt = count($ccAddPersons) + count($ccAddNewPersons);
                if ($cnt > $maxCc) {
                    $errors['cc_limit'] = true;
                }
            }

            if ($errors) {
                $errors = array_keys($errors);

                return $this->createJsonResponse(['error' => true, 'error_codes' => $errors]);
            }

            $newTicket->ticket_fields             = $this->request->request->get('custom_fields', []);
            $newTicket->post_custom_person_fields = $this->request->request->get('custom_person_fields', []);
            $newTicket->post_custom_org_fields    = $this->request->request->get('custom_org_fields', []);
            $newTicket->billing_fields            = $this->request->request->get('billing_fields', []);
            $newTicket->status                    = $set_status;

            $billingAmount = null;
            $billingTime   = null;
            if ($this->settings->get('core_tickets.enable_billing') || $this->settings->get(
                    'core_tickets.enable_timelog'
                )
            ) {
                if ($this->in->getString('billing_type') == 'amount') {
                    $billingAmount = $this->in->getFloat('amount');
                } else {
                    $billingTime = (
                        3600 * $this->in->getUInt('hours')
                        + 60 * $this->in->getUInt('minutes')
                        + $this->in->getUInt('seconds')
                    );
                }
            }

            // Validate based on department...
            $validator = new NewTicketValidator();
            $layout    = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout(
                $newTicket->department_id
            );
            $layout = LayoutDisplay::createFromLayout(
                $layout,
                LayoutDisplay::NEW_TICKET,
                $newTicket->getMockTicket()
            );

            if (isset($check_person) && $check_person) {
                $newTicket->setValuesFromTicket(null, $check_person, $check_person->organization);
            }

            $validator->setLayout($layout);
            $newTicket->setLayout($layout);

            $all_billing_errors = [];
            if (($billingAmount || $billingTime) && ($post_billing_fields = $request->get('billing_fields', []))) {
                $billing_field_manager = $this->container->getBillingFieldManager();

                foreach ($billing_field_manager->getFields() as $field) {
                    $billing_errors = $field->getHandler()->validateFormData(
                        $post_billing_fields,
                        HandlerAbstract::CONTEXT_AGENT
                    );
                    foreach ($billing_errors as $code) {
                        $all_billing_errors[] = '(Billing) '.$field['title'].': '.$this->container->getTranslator(
                            )->getPhraseText(
                                preg_replace('#^(.*?)\.#', 'user.error.form_', $code)
                            );
                    }
                }
            }

            if (!$validator->isValid($newTicket) || $all_billing_errors) {
                $free = [];
                foreach ($validator->getErrorsInfo() as $info) {
                    $free[] = $info['message'];
                }
                $free = array_merge($free, $all_billing_errors);

                return $this->createJsonResponse(
                    ['error' => true, 'error_codes' => ['free' => true], 'error_messages' => $free]
                );
            }

            //------------------------------
            // Add Followers
            //------------------------------

            $add_followers = $this->in->getCleanValueArray('add_followers', 'uint', 'discard');
            $add_followers = ListUtils::filterOutFalsey($add_followers);
            if ($add_followers) {
                $newTicket->add_followers = $add_followers;
            }

            //------------------------------
            // Save
            //------------------------------

            $this->db->beginTransaction();

            try {
                $comment_type   = $this->in->getString('for_comment_type');
                $comment_id     = $this->in->getUInt('for_comment_id');
                $comment_action = $this->in->getString('comment_action');
                $comment        = null;

                if ($comment_id && $comment_type && $comment_action) {
                    $entity  = $this->_getCommentEntityName($comment_type);
                    $comment = $this->em->find($entity, $comment_id);
                }

                if ($comment) {
                    $newTicket->setPreSaveCallback(
                        function (Ticket $ticket) use ($comment, $comment_type, $comment_id, $comment_action) {
                            $ticket->getTicketLogger()->recordExtra(
                                'created_via_comment',
                                [
                                    'comment_type'          => $comment_type,
                                    'comment_id'            => $comment_id,
                                    'comment_action'        => $comment_action,
                                    'comment_content_id'    => $comment->getObject()->getId(),
                                    'comment_content_title' => $comment->getObject()->getTitle(),
                                ]
                            );
                        }
                    );
                }

                $newTicket->add_cc_person    = $this->in->getCleanValueArray('newticket.add_cc_person', 'uint');
                $newTicket->add_cc_newperson = $this->in->getCleanValueArray(
                    'newticket.add_cc_newperson',
                    'raw',
                    'discard'
                );

                $newTicket->save();
                $ticket = $newTicket->getTicket();

                $this->em->persist($ticket);

                if ($this->in->getUInt('parent_ticket_id')) {
                    $parent_ticket = $this->em->find(Ticket::class, $this->in->getUInt('parent_ticket_id'));
                    if ($parent_ticket) {
                        $ticket->setParentTicket($parent_ticket);
                    }
                }

                $this->em->flush();

                if ($collection->countActions()) {
                    $collection->apply($ticket->getTicketLogger(), $ticket, $this->person);
                    $this->em->flush();
                }

                //------------------------------
                // Labels
                //------------------------------

                $labels = $this->in->getCleanValue('labels');
                $ticket->getLabelManager()->addLabels(explode(',', $labels));
                $this->em->flush();

                //------------------------------
                // Billing/time
                //------------------------------

                if ($billingAmount || $billingTime) {
                    if ($charge = $ticket->addCharge($this->person, $billingTime, $billingAmount)) {
                        $this->em->persist($charge);
                        $this->em->persist($ticket);
                        $this->em->flush();

                        $ticket_log              = new TicketLog();
                        $ticket_log->ticket      = $ticket;
                        $ticket_log->person      = $this->person;
                        $ticket_log->action_type = 'new_billing';
                        $ticket_log->details     = [
                            'new_amount' => $charge->getAmount(),
                            'new_time'   => $charge->getChargeTime(),
                        ];
                        $this->em->persist($ticket_log);

                        if (!empty($post_billing_fields)) {
                            $billing_field_manager->saveFormToObject($post_billing_fields, $charge);
                            $changes = $charge->getStateChangeRecorder()->getChanges();

                            $class      = 'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator';
                            $serialized = sprintf('O:%u:"%s":0:{}', strlen($class), $class);
                            $obj        = unserialize($serialized);
                            $method     = new \ReflectionMethod(
                                'Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator', 'getLogDataForChange'
                            );
                            $method->setAccessible(true);
                            $details = $ticket_log->details;

                            foreach ($changes as $change) {
                                if (0 !== strpos($change->getField(), 'custom_data.')) {
                                    continue;
                                }
                                $details['custom_data'][$change->getField()] = $method->invoke($obj, $change);
                            }
                        }

                        $details['charge_id']  = $charge->getId();
                        $ticket_log->id_object = $charge->getId();
                        $ticket_log->details   = $details;
                        $this->em->flush();
                    }
                }

                //------------------------------
                // Add CC's
                //------------------------------

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
                        if ($email && !StringEmail::isValueValid($email)) {
                            continue;
                        }

                        $p = Person::newContactPerson(
                            [
                                'name'  => $name,
                                'email' => $email,
                            ]
                        );
                        $this->em->persist($p);
                        $this->em->flush();

                        $part = $ticket->addParticipantPerson($p);
                        if ($part) {
                            $this->em->persist($part);
                        }
                    }

                    $this->em->flush();
                }

                //------------------------------
                // Related chat
                //------------------------------

                $chat_id = $this->in->getUInt('for_chat_id');
                $chat    = null;
                if ($chat_id) {
                    $chat = $this->em->find(ChatConversation::class, $chat_id);

                    $ticket->linked_chat = $chat;
                    $this->em->persist($ticket);
                    $this->em->flush();
                }

                //------------------------------
                // Related comment
                //------------------------------

                if ($comment) {
                    switch ($comment_action) {
                        case 'delete':
                            $this->em->remove($comment);
                            break;
                        case 'approve':
                            $comment->setStatus('visible');
                            $this->em->persist($comment);
                            break;
                    }

                    $this->em->flush();
                }

                if ($this->settings->get('core.problems.enabled')) {
                    $id    = (int) $request->get('problem_id');
                    $title = $this->in->getString('problem_title'); // sanitize
                    /** @var TicketChecker $checker */
                    $checker = $this->person->PermissionsManager->TicketChecker;
                    if ($checker->canAssociateProblem($ticket)) {
                        if (-1 === $id && $title) {
                            if ($this->person->hasPerm('agent_problems.create')) {
                                $problem = new Problem();
                                $problem->setCreator($this->person)->setTitle($title);
                                $this->em->persist($problem);
                                $this->em->flush();
                            }
                        } else {
                            if (!$problem = $this->em->find(Problem::class, $id)) {
                                // silent?
                            }
                        }

                        if (isset($problem) && $problem->isOpen()) {
                            $ticket->associateProblem($problem);
                            $this->em->flush();
                        }
                    }
                }

                if ($snippetIds = $this->in->getString('options.snippet_ids')) {
                    $snippetIds = explode(',', $snippetIds);
                    $snippetIds = array_map(
                        function ($x) {
                            return (int) trim($x);
                        },
                        $snippetIds
                    );
                    $snippetIds = Arrays::removeFalsey($snippetIds);
                    $snippetIds = array_unique($snippetIds, SORT_NUMERIC);

                    foreach ($snippetIds as $snippetId) {
                        if ($this->container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
                            // $snippetId refers here to the SnippetTranslation id
                            $snippetTranslation = $this->em->find(SnippetTranslation::class, $snippetId);

                            if ($snippetTranslation) {
                                $messages = $ticket->getMessages();

                                $message = $messages->last();

                                $snippetLog = SnippetUseLog::createSnippetTicketLog($message, $this->getPerson(), $snippetTranslation);
                                $snippet    = $snippetLog->getSnippet();
                                $snippet->setUsageCount((int) $snippet->getUsageCount() + 1);
                                $this->em->persist($snippet);
                                $this->em->persist($snippetLog);
                                $this->em->flush();
                            }
                        } else {
                            $snippet = $this->em->find(TextSnippet::class, $snippetId);

                            if ($snippet) {
                                $snippetLog = Entity\TicketObjectUseLog::createSnippetLog(
                                    $ticket,
                                    $this->getPerson(),
                                    $snippet
                                );
                                $this->em->persist($snippetLog);
                                $this->em->flush();
                            }
                        }
                    }
                }

                if ($macro) {
                    $macroLog = Entity\TicketObjectUseLog::createMacroLog($ticket, $this->getPerson(), $macro);
                    $this->em->persist($macroLog);
                }

                $ticket->recomputeHash();
                if ($dupe_ticket = $this->em->getRepository(Ticket::class)->checkDupeTicket($ticket)) {
                    $e            = new DuplicateTicketException();
                    $e->ticket_id = $dupe_ticket->id;
                    throw $e;
                }

                $this->db->commit();
            } catch (DuplicateTicketException $e) {
                $this->db->rollback();

                return $this->createJsonResponse(
                    [
                        'error'          => true,
                        'is_dupe'        => true,
                        'dupe_ticket_id' => $e->ticket_id,
                    ]
                );
            } catch (\Exception $e) {
                $this->db->rollback();
                // pass $noShowErrors = true to not `echo` error in browser
                SystemErrorHandler::logException($e, false, null, true);

                return $this->createJsonResponse(
                    [
                        'error'   => true,
                        'message' => $e->getMessage(),
                    ],
                    400
                );
            }

            return $this->createJsonResponse(
                [
                    'success'      => true,
                    'ticket_id'    => $ticket['id'],
                    'can_view'     => $this->person->PermissionsManager->TicketChecker->canView($ticket),
                    'comment_id'   => $comment_id,
                    'comment_type' => $comment_type,
                ]
            );
        }

        return $this->createJsonResponse(
            [
                'success' => false,
            ]
        );
    }

    protected function _getCommentEntityName($typename)
    {
        switch ($typename) {
            case 'articles':
                return ArticleComment::class;
            case 'downloads':
                return DownloadComment::class;
            case 'news':
                return NewsComment::class;
            case 'feedback':
                return FeedbackComment::class;
        }
    }

    public function newticketGetPersonRowAction($person_id)
    {
        if (!$person_id && $this->in->getUInt('person_id')) {
            $person_id = $this->in->getUInt('person_id');
        }

        $person = false;
        if ($person_id) {
            $person = $this->em->find(Person::class, $person_id);
        }
        if (!$person && $this->in->getString('email')) {
            $person = $this->container->getSystemService('UsersourceManager')->findPersonByEmail(
                $this->in->getString('email')
            );
        }

        $session = null;
        if ($this->in->getUInt('session_id')) {
            $session = $this->em->find(Session::class, $this->in->getUInt('session_id'));
        }
        if ($session && $session->getPerson()) {
            $person = $session;
        }

        if (!$person) {
            $person = new Person();
        }

        $api_data = $person->toApiData();

        return $this->render(
            'AgentBundle:Ticket:newticket-person-row.html.twig',
            [
                'person'   => $person,
                'api_data' => $api_data,
            ]
        );
    }

    /**
     * refresh custom fields for changed person context.
     *
     * @param $person_id
     * @param $department_id
     *
     * @return Response
     */
    public function newTicketGetCustomFieldsRowAction($person_id, $department_id)
    {
        if (!$person = $this->em->find('DeskPRO:Person', (int) $person_id)) {
            $person = new Person(); // mock
        }

        $layouts = $this->container->getTicketLayoutManager()->getAgentLayouts();
        $layout  = LayoutDisplay::createFromLayout($layouts->getLayout($department_id), LayoutDisplay::NEW_TICKET);

        $manager           = $this->container->getCustomFieldManager();
        $mock              = new Entity\Ticket();
        $new_custom_fields = $manager->createFormForOwner($mock, $person, $layout, ['allow_edit' => true]);
        if ($org = $person->organization) {
            $manager->merge(
                $new_custom_fields,
                $manager->createFormForOwner($mock, $org, $layout, ['allow_edit' => true])
            );
        }
        $custom_person_fields_form = $this->get('form.factory')->createNamedBuilder('custom_person_fields', 'form');
        $custom_org_fields_form    = $this->get('form.factory')->createNamedBuilder('custom_org_fields', 'form');
        $custom_person_fields      = $this->container->getPersonFieldManager()->getDisplayArrayForObject(
            $person,
            $custom_person_fields_form
        );
        $custom_org_fields = $person->organization
            ? $this->container->getOrgFieldManager()->getDisplayArrayForObject(
                $person->organization,
                $custom_org_fields_form
            )
            : [];

        return $this->render(
            'AgentBundle:Ticket:newticket-custom-fields-row.html.twig',
            [
                'new_custom_fields'    => $new_custom_fields->createView(),
                'custom_person_fields' => $custom_person_fields,
                'custom_org_fields'    => $custom_org_fields,
            ]
        );
    }

    public function lockTicketAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if ($ticket->hasLock()) {
            return $this->createJsonResponse(
                [
                    'error' => true,
                ]
            );
        }

        $ticket->setLockedByAgent($this->person);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function unlockTicketAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if (!$ticket->hasLock()) {
            return $this->createJsonResponse(['success' => true]);
        }

        $ticket->setLockedByAgent(null);
        $this->em->persist($ticket);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function releaseLockAction($ticket_id)
    {
        // not using $this->getTicketOr404
        // because we may need to unlock the ticket from
        // an agent who no longer has permission to see it
        $ticket = $this->em->find(Ticket::class, $ticket_id);

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        if ($ticket->hasLock() && $ticket->locked_by_agent->id == $this->person->id) {
            $ticket->setLockedByAgent(null);
            $this->em->persist($ticket);
            $this->em->flush();
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function updateDraftsAction()
    {
        $ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');

        $tickets = $this->em->getRepository(Ticket::class)->getByIds($ticket_ids);
        $drafts  = $this->em->getRepository(Draft::class)->getActiveDrafts('ticket', $ticket_ids);

        $output = [];
        foreach ($tickets as $ticket) {
            if (empty($drafts[$ticket->id])) {
                continue;
            }
            if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
                continue;
            }

            $output[$ticket->id] = $this->_renderActiveDrafts($ticket, $drafts[$ticket->id]);
        }

        return $this->createJsonResponse(
            [
                'drafts' => $output,
            ]
        );
    }

    protected function _renderActiveDrafts(Ticket $ticket, array $drafts)
    {
        $output = [];

        unset($drafts[$this->person->id]);
        foreach ($drafts as $id => $draft) {
            $output[] = $this->renderView(
                'AgentBundle:Ticket:ticket-message-draft.html.twig',
                [
                    'draft'  => $draft,
                    'ticket' => $ticket,
                ]
            );
        }

        return $output;
    }

    //###########################################################################
    // download-ticket-debug
    //###########################################################################

    public function downloadTicketDebugAction($ticket_id)
    {
        if (!$this->person->can_admin) {
            throw $this->createNotFoundException();
        }

        $ticket = $this->getTicketOr404($ticket_id);

        $tmpdir = dp_get_tmp_dir().DIRECTORY_SEPARATOR.'ticket-debug-'.$ticket->id.'_'.date(
                'YmdHis'
            ).'_'.DpStrings::random(4, Strings::CHARS_ALPHANUM_IU);
        if (!mkdir($tmpdir, 0777, true)) {
            echo 'Could not create temp dir: '.$tmpdir;
            exit;
        }

        $emailSettings = new EmailAccountsSettings($this->get('deskpro.core.settings'));
        file_put_contents($tmpdir.'/email_accounts_settings.json', json_encode($emailSettings->toArray()));

        $d = new TicketTriggerData();
        file_put_contents($tmpdir.'/triggers.json', json_encode($d->getData()));

        $d = new TicketFilterData();
        file_put_contents($tmpdir.'/filters.json', json_encode($d->getData()));

        $d = new TicketLayoutsData();
        file_put_contents($tmpdir.'/ticket-layouts.json', json_encode($d->getData()));

        $d = new TicketContextData();
        file_put_contents($tmpdir.'/ticket-context.json', json_encode($d->getData()));

        $d = new TicketData($ticket);
        file_put_contents($tmpdir.'/ticket.json', json_encode($d->getData()));

        $d = new TicketPersonData($ticket);
        file_put_contents($tmpdir.'/person.json', json_encode($d->getData()));

        $d = new TicketLogsData($ticket);
        file_put_contents($tmpdir.'/ticket-log.json', json_encode($d->getData()));

        $info = $this->_getTicketLogsBlockInfo($ticket, 1, null, 999999, 999999);
        $css  = <<<'CSS'
<style>
body {
	font-family: Helvetica, Verdana, Arial, sans-serif;
	line-height: 125%;
	font-size: 10pt;
}

.section-subnav {
	background: #ccc;
	padding: 30px;
	margin-bottom: 15px;
}

.section-subnav ul {
	margin: 0;
	padding: 0;
}

.section-subnav li {
	display: inline;
	margin-right: 10px;
}

.section-subnav li em {
	font-style: normal;
}

.dp-is-loading {
	display: none;
}

.log-batch {
	border: 1px solid #ddd;
	margin-bottom: 10px;
}

.type-action_starter {
	background: #eee;
}

.log-row {
	padding: 10px;
	border-top: 1px solid #eee;
}

.log-row .info {
	float: right;
}

time {
	float: right;
}

.expand-set {
	display: block !important;
	margin-left: 15px;
	font-size: 90%;
}
</style>
CSS;

        file_put_contents($tmpdir.'/ticket-log.html', $css.$info['rendered']);
        unset($info);

        foreach ($ticket->messages as $message) {
            $data = $message->toApiData();

            if (count($message->attachments)) {
                $data['attachments'] = [];
                foreach ($message->attachments as $attach) {
                    $attach_data = $attach->toArray();
                    unset($attach_data['ticket'], $attach_data['message'], $attach_data['person'], $attach_data['visitor']);
                    $attach_data['blob']   = $attach->getBlob()->toArray();
                    $data['attachments'][] = $attach_data;
                }
            }

            file_put_contents($tmpdir.'/message-'.$message->id.'.json', json_encode($data));

            if ($message->email_source && $message->email_source->getBlob()) {
                try {
                    $this->container->getBlobStorage()
                        ->copyBlobRecordToFile(
                            $tmpdir.'/message-'.$message->id.'-source.eml',
                            $message->email_source->getBlob()
                        );
                } catch (\Exception $e) {
                    file_put_contents(
                        $tmpdir.'/message-'.$message->id.'-source.eml',
                        "Could not download blob: {$e->getMessage()}"
                    );
                }
            }

            if ($message->email_source && $message->email_source->getSourceInfo()) {
                file_put_contents(
                    $tmpdir.'/message-'.$message->id.'.source_info.txt',
                    $message->email_source->getSourceInfoAsString()
                );
            }

            if ($message->email_source && $message->email_source->getLogBlob()) {
                $fileName = $tmpdir.'/message-'.$message->id.'.log';
                if (strpos($message->email_source->getLogBlob()->getContentType(), 'gzip') !== false) {
                    $fileName .= '.gz';
                }
                $this->container->getBlobStorage()
                    ->copyBlobRecordToFile(
                        $fileName,
                        $message->email_source->getLogBlob()
                    );
            }

            if ($message->email_source && $message->email_source->getEmailAccountLog()) {
                $this->container->getBlobStorage()
                    ->copyBlobRecordToFile(
                        $tmpdir.'/message-'.$message->id.'.email_account_session.log',
                        $message->email_source->getEmailAccountLog()->getBlob()
                    );
            }
        }

        $tm_logs = $this->em->createQuery(
            '
            SELECT tm_log, b
            FROM DeskPRO:TicketProcLog tm_log
            LEFT JOIN tm_log.blob b
            WHERE tm_log.ticket = ?0
        '
        )->execute([$ticket]);

        foreach ($tm_logs as $tm_log) {
            try {
                $this->container->getBlobStorage()->copyBlobRecordToFile(
                    $tmpdir.'/'.$tm_log->blob->filename,
                    $tm_log->blob
                );
            } catch (\Exception $e) {
                file_put_contents(
                    $tmpdir.'/message-'.$message->id.'-source.eml',
                    "Could not download blob: {$e->getMessage()}"
                );
            }
        }

        $outfile = $tmpdir.'/zip';

        require_once DP_ROOT.'/vendor-src/pclzip/pclzip.lib.php';
        $zip = new \PclZip($outfile);
        $zip->add(
            $tmpdir,
            \PCLZIP_OPT_REMOVE_PATH,
            dirname($tmpdir)
        );

        header('Content-Type: application/zip; filename=ticket-debug-'.$ticket->id.'.zip');
        header('Content-Length: '.filesize($outfile));
        header('Content-Disposition: attachment; filename=ticket-debug-'.$ticket->id.'.zip');

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

    //###########################################################################
    // download-ticket-message-email
    //###########################################################################

    /**
     * @param int $messageId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function downloadTicketMessageEmailAction($messageId)
    {
        $message = $this->getMessageOr404($messageId);
        if ($message && $this->person->PermissionsManager->TicketChecker->canView($message->ticket)) {
            $ticket = $message->ticket;
        }

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        if ($message->email_source && $message->email_source->getBlob()) {
            $fileString = $this->container->getBlobStorage()->copyBlobRecordToString($message->email_source->getBlob());
            $response   = new Response();
            $response->headers->set('Content-Type', 'message/rfc822');
            $response->headers->set('Content-Disposition', 'inline; filename=email_source_'.$message->getId().'.eml');
            $response->setContent($fileString);

            return $response;
        } else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param $messageId
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteTicketMessageEmailAction($messageId)
    {
        $message = $this->getMessageOr404($messageId);
        if (!$this->person->PermissionsManager->TicketChecker->canEditMessages($message->ticket)) {
            throw $this->createAccessDeniedException();
        }

        if ($message->email_source) {
            $this->em->remove($message->email_source);
            $this->em->flush();
        }

        return $this->createJsonResponse(
            [
                'success' => true,
            ]
        );
    }

    //###########################################################################

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
        } elseif ($check_perm == 'view') {
            if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
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

        return array_filter(
            $tickets,
            function ($t) use ($self, $check_perm) {
                return $self->checkPerm($t, $check_perm);
            }
        );
    }

    /**
     * @param int  $ticket_id
     * @param null $check_perm
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Ticket
     */
    protected function getTicketOr404($ticket_id, $check_perm = null)
    {
        $q = $this->em->createQuery(
            '
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
        '
        );
        $q->setParameters([$ticket_id]);

        $ticket = $q->getOneOrNullResult();

        // If no ticket, check the delete log in case it was merged since
        if (!$ticket) {
            $merged_ticket_id = $this->em->getRepository(Ticket::class)->findTicketId($ticket_id);
            if ($merged_ticket_id) {
                return $this->getTicketOr404($merged_ticket_id, $check_perm);
            }
        }

        if (!$ticket) {
            throw $this->createNotFoundException("There is no ticket with ID $ticket_id");
        }

        if (!$this->person->PermissionsManager->TicketChecker->canView($ticket)) {
            throw new AccessDeniedHttpException('You are not allowed to view this ticket');
        }

        if ($check_perm && !$this->checkPerm($ticket, $check_perm)) {
            throw new AccessDeniedHttpException("There is no ticket with ID $ticket_id");
        }

        return $ticket;
    }

    /**
     * @param int $messageId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return TicketMessage
     */
    protected function getMessageOr404($messageId)
    {
        $q = $this->em->createQuery(
            '
            SELECT m
            FROM DeskPRO:TicketMessage m
            WHERE m.id = ?0
        '
        );
        $q->setParameters([$messageId]);

        $message = $q->getOneOrNullResult();

        if (!$message) {
            throw $this->createNotFoundException("There is no message with ID $messageId");
        }

        return $message;
    }

    public function linkExistingAction($ticket_id, $linked_ticket_id)
    {
        try {
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository(TicketDeleted::class)->findOneBy(['ticket_id' => $ticket_id]);
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', ['delete_log' => $delete_log]);
            } else {
                throw $e;
            }
        }

        try {
            $linkedTicket = $this->getTicketOr404($linked_ticket_id);
        } catch (NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository(TicketDeleted::class)->findOneBy(['ticket_id' => $linked_ticket_id]);
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', ['delete_log' => $delete_log]);
            } else {
                throw $e;
            }
        }

        if ($ticket === $linkedTicket) {
            return $this->createJsonResponse([
                'success' => false,
                'error'   => 'Unable to link ticket to itself.',
            ]);
        }
        if ($ticket->getChildrenTickets()->contains($linkedTicket) || $ticket->getParentTicket() === $linkedTicket) {
            return $this->createJsonResponse([
                'success' => false,
                'error'   => 'Tickets are already linked.',
            ]);
        }

        if ($this->in->getBool('isParent')) {
            $ticket->setParentTicket($linkedTicket);
        } else {
            $linkedTicket->setParentTicket($ticket);
        }

        $this->em->persist($ticket);
        $this->em->persist($linkedTicket);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    public function linkExistingOverlayAction($ticket_id)
    {
        try {
            $this->getTicketOr404($ticket_id);
        } catch (NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository(TicketDeleted::class)->findOneBy(['ticket_id' => $ticket_id]);
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', ['delete_log' => $delete_log]);
            } else {
                throw $e;
            }
        }

        return $this->render('AgentBundle:Ticket:link.html.twig');
    }

    public function linkExistingFeedbackOverlayAction($ticket_id)
    {
        try {
            $ticket = $this->getTicketOr404($ticket_id);
        } catch (NotFoundHttpException $e) {
            // try to find a delete log
            $delete_log = $this->em->getRepository(TicketDeleted::class)->findOneBy(['ticket_id' => $ticket_id]);
            if ($delete_log) {
                return $this->render('AgentBundle:Ticket:deleted.html.twig', ['delete_log' => $delete_log]);
            } else {
                throw $e;
            }
        }

        $exludeIds = $ticket->getFeedbackLinks()->map(function ($e) {
            return $e->getFeedback()->getId();
        })->toArray();

        return $this->render('AgentBundle:Ticket:link-feedback.html.twig', [
            'ticket'    => $ticket,
            'exludeIds' => $exludeIds,
        ]);
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
                $linked_ticket = $this->em->find(Ticket::class, $this->in->getUInt('link_ticket_id'));
                break;
        }

        if (!$linked_ticket) {
            throw $this->createNotFoundException();
        }

        $linked_ticket->setParentTicket();
        $this->em->persist($linked_ticket);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @param $ticket_id
     *
     * @return Response
     */
    public function closeProblemAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if (!$problem = $ticket->getProblems()->first()) {
            throw new NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_problems.close')) {
            throw new AccessDeniedHttpException();
        }

        $problem['is_open'] = false;
        $this->em->flush();

        $data = [];

        return $this->createJsonResponse($data);
    }

    /**
     * @param $ticket_id
     *
     * @return Response
     */
    public function reopenProblemAction($ticket_id)
    {
        $ticket = $this->getTicketOr404($ticket_id);

        if (!$problem = $ticket->getProblems()->first()) {
            throw new NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_problems.reopen')) {
            throw new AccessDeniedHttpException();
        }

        $problem['is_open'] = true;
        $this->em->flush();

        $data = [];

        return $this->createJsonResponse($data);
    }

    public function ajaxGetDepartmentsAction($brandId)
    {
        /** @var Brand $brand */
        $brand       = $this->em->getRepository(Brand::class)->find($brandId);
        $departments = $this->container->getDataService('Department')
            ->getPersonDepartments($this->person, 'tickets', [], 'assign', $brand);

        return $this->render(
            'AgentBundle:Common:select-department.html.twig',
            [
                'name'        => 'newticket[department_id]',
                'id'          => 'dep',
                'departments' => $departments,
                'add_attr'    => 'data-style-type="icons" data-select-icon-size="22"',
                'with_blank'  => true,
            ]
        );
    }

    /**
     * @return Entity\Brand[]
     */
    protected function getAgentBrands()
    {
        /** @var Entity\Department[] $departments */
        $departments = $this->getContainer()
            ->getTicketDepartments()
            ->getByIds($this->person->AgentPermissions->getAllowedDepartments('tickets', false, 'assign'));

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $key => $brand) {
            $department_found = false;
            foreach ($departments as $department) {
                if ($department->hasBrand($brand)) {
                    $department_found = true;
                    break;
                }
            }
            if (!$department_found) {
                unset($brands[$key]);
            }
        }

        return $brands;
    }

    /**
     * @param $ticket
     *
     * @return Entity\EmailAccount|null
     */
    protected function getAccount($ticket)
    {
        $account = null;

        if ($this->container->getSetting('core_tickets.fwd_use_account')) {
            try {
                $account = $this->container->getEmailAccountManager()->getAccount(
                    $this->container->getSetting('core_tickets.fwd_use_account')
                );
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
            $account = $this->container->getEmailAccountManager()->getPrimaryTicketAccountWithFallback();
        }

        return $account;
    }

    /**
     * @param LayoutDisplay $layout
     * @param string        $fieldType
     * @param array         $submittedData
     *
     * @return array
     */
    private function filterSubmittedLayoutData(LayoutDisplay $layout, $fieldType, $submittedData)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $layoutCustomData = [];
        foreach ($layout->all() as $layoutField) {
            if ($layoutField->getFieldType() === $fieldType) {
                $fieldId   = $layoutField->getFieldId();
                $fieldName = 'field_'.$fieldId;

                if (isset($submittedData[$fieldName])) {
                    $layoutCustomData[$fieldName] = $submittedData[$fieldName];
                } else {
                    if ($fieldId) {
                        $customField = null;
                        switch ($layoutField->getFieldType()) {
                            case 'ticket_field':
                                $customField = $em->getRepository(Entity\CustomDefTicket::class)->find($fieldId);
                                break;
                            case 'user_field':
                                $customField = $em->getRepository(Entity\CustomDefPerson::class)->find($fieldId);
                                break;
                            case 'org_field':
                                $customField = $em->getRepository(Entity\CustomDefOrganization::class)->find($fieldId);
                                break;
                        }

                        if ($customField instanceof Entity\CustomDefAbstract) {
                            if ($customField->isMulti()) {
                                $layoutCustomData[$fieldName] = [];
                            }
                            if ($customField->isToggleType()) {
                                $layoutCustomData[$fieldName] = 0;
                            }
                        }
                    }
                }
            }
        }

        return $layoutCustomData;
    }
}
