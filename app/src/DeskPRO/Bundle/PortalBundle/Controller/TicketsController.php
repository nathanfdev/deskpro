<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\People\PersonGuest;
use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use DeskPRO\Bundle\PortalBundle\Routing\RedirectToUrlException;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTablesCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TicketsController extends AbstractController
{
    /**
     * @Route("/tickets/resolved/{type}", name="portal_tickets_resolved", defaults={"type":"own", "resolved_only": true}, requirements={"type":"organization"})
     * @Route("/tickets/{type}", name="portal_tickets", defaults={"type":"own"}, requirements={"type":"organization"})
     * @Route("/tickets", name="user_tickets")
     * @Route("/tickets/organization", name="user_tickets_organization", defaults={"type":"organization"})
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function indexAction(Request $request, $type, $resolved_only = false)
    {
        $person = $this->getUser();

        // access to organization list?
        if ($type === 'organization' && !($person->organization && $person->organization_manager)) {
            return $this->redirectToRoute('portal_tickets');
        }

        // create ticket list tables
        /* @var TicketListTable[] $tables */
        $ticket_categories = $resolved_only ?
            [
                TicketFilter::CATEGORY_RESOLVED => $this->phrase('portal.tickets.list_status_resolved'),
            ]
            :
            [
                TicketFilter::CATEGORY_AWAITING_USER  => $this->phrase('portal.tickets.list_status_user'),
                TicketFilter::CATEGORY_AWAITING_AGENT => $this->phrase('portal.tickets.list_status_agent'),
            ];
        $tables = $this->makeTicketListTables($type,  $ticket_categories, $person, $request);

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketList();

        $ticket_list_js = 'window.DESKPRO_TICKET_LIST_TABLES = '.$tables->compileJsObj().';';

        return $this->renderThemeView(
            'Theme:Tickets:index.html.twig',
            array(
                'ticket_list_tables' => $tables,
                'resolved_only'      => $resolved_only,
                'open_ticket_count'  => $this->getTicketsDataService()->getTicketCount($person, 'open'),
                'type'               => $type,
                'person'             => $person,
                'breadcrumbs'        => $breadcrumbs,
                'page_title'         => $this->createPageTitle()->tickets(),
                'ticket_list_js'     => $ticket_list_js,
                'search_query'       => $request->query->get('q', ''),
            )
        );
    }

    /**
     * @param $type
     * @param array   $categories
     * @param Person  $person
     * @param Request $request
     *
     * @return TicketListTablesCollection
     */
    protected function makeTicketListTables($type, array $categories, Person $person, Request $request)
    {
        $tables = new TicketListTablesCollection();

        foreach ($categories as $category => $title) {
            $tables->addTable(
                $this->get('tickets.table')->makeTicketTable($person, $request, $type, $category, $title)
            );
        }

        return $tables;
    }

    /**
     * @Route("/tickets/{ticket_ref}", name="portal_tickets_view")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function viewAction(Request $request, $ticket_ref, $visitor_id)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $form_data = array(
            'ticket_message' => $message = new TicketMessage(),
            'attachments'    => new ArrayCollection(),
        );

        $message->setVisitorId($visitor_id);
        $message->setIpAddress($request->getClientIp());

        $form = $this->createForm('ticket_reply', $form_data, array(
            'ticket'         => $ticket,
            'ticket_message' => $message,
            'person'         => $this->getUser(),
            'settings'       => $this->getBrandContainer()->getSettings(),
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton()->getConfig()->getName() !== 'more_attachments') {
                // We don't continue here if they just clicked the "add more attachments" button
                $this->saveNewReply($ticket, $message);

                $this->addFlash('success', $this->phrase('portal.flashes.ticket_replied'));

                return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
            }
        }

        $ticket_view = $this->getTicketsViewService()->getUserTicketView($ticket);

        $timeline = $this->get('data.ticket_timeline')->getUserTimeline($ticket);

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketView($ticket);

        list($last_user_reply_in_seconds, $created_in_seconds) = $this->getRecentTimes($ticket);

        return $this->renderThemeView(
            'Theme:Tickets:view.html.twig',
            array(
                'ticket'                     => $ticket,
                'ticket_view'                => $ticket_view,
                'timeline'                   => $timeline,
                'can_edit'                   => $this->isGranted('TICKET_EDIT', $ticket),
                'form'                       => $form->createView(),
                'breadcrumbs'                => $breadcrumbs,
                'page_title'                 => $this->createPageTitle()->tickets($ticket),
                'last_user_reply_in_seconds' => $last_user_reply_in_seconds,
                'created_in_seconds'         => $created_in_seconds,
                'edit_page'                  => false,
                'form_errors'                => $form->isSubmitted() ? $form->getErrors() : [],
            )
        );
    }

    /**
     * @Route("/tickets/{ticket_ref}/edit", name="portal_tickets_edit")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function editAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_EDIT, $ticket)) {
            throw new AccessDeniedException();
        }

        $person = $this->getUser();

        $form = $this->createForm('ticket', $ticket, array(
            'person'            => $person,
            'ticket_visibility' => 'edit',
            'settings'          => $this->getBrandContainer()->getSettings(),
        ));

        $rerendering = false;
        if ($form->has('rerender_form')) {
            $rerendering = true;
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            // if the form set a hidden field "rerender_form" then we want to skip actual processing for now
            if (!$form->has('rerender_form')) {
                $this->saveEditedTicket($ticket, $person);

                $this->addFlash('success', $this->phrase('portal.flashes.ticket_updated'));

                return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
            }
        }

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketEdit($ticket);
        $ticket_view = $this->getTicketsViewService()->getUserTicketView($ticket);

        list($last_user_reply_in_seconds, $created_in_seconds) = $this->getRecentTimes($ticket);

        $form_full = $this->createForm('ticket', $ticket, array(
            'person'            => $person,
            'ticket_message'    => null,
            'settings'          => $this->getBrandContainer()->getSettings(),
            'full_version'      => true,
            'ticket_visibility' => 'edit',
            'action'            => $this->generateUrl('portal_new_ticket'),
        ));
        $layouts           = $this->getContainer()->getTicketLayoutManager()->getUserLayouts(true);
        $ticket_display_js = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        return $this->renderThemeView(
            'Theme:Tickets:edit.html.twig',
            array(
                'ticket'                     => $ticket,
                'form'                       => $form->createView(),
                'rerendering'                => $rerendering,
                'breadcrumbs'                => $breadcrumbs,
                'page_title'                 => $this->createPageTitle()->tickets($ticket),
                'last_user_reply_in_seconds' => $last_user_reply_in_seconds,
                'created_in_seconds'         => $created_in_seconds,
                'edit_page'                  => true,
                'can_edit'                   => $this->isGranted('TICKET_EDIT', $ticket),
                'ticket_view'                => $ticket_view,
                'ticket_display_js'          => $ticket_display_js,
                'form_full'                  => $form_full->createView(),
            )
        );
    }

    /**
     * @Route("/tickets/{ticket_ref}/resolve", name="portal_tickets_resolve")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function resolveTicketAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        // already resolved, no need to proceed
        if ($ticket->isResolved()) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        $person = $this->getUser();

        if ('POST' === $request->getMethod()) {
            $ticket->setStatus(Ticket::STATUS_RESOLVED);
            $this->saveEditedTicket($ticket, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.ticket_resolved'));

            // TODO: when we do feedback, we'd want to show them that form now

            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        return $this->renderThemeView('Theme:Tickets:resolve.html.twig', array(
            'ticket'      => $ticket,
            'breadrcumbs' => $this->getBreadcrumbGenerator()->buildTicketEdit($ticket),
            'page_title'  => $this->createPageTitle()->tickets($ticket),
        ));
    }

    /**
     * @Route("/tickets/{ticket_ref}/add-cc", name="portal_tickets_cc_add")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function addCcAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $redirect_response = $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $ticket_ref]);

        $name  = $request->request->get('name');
        $email = $request->request->get('email');

        if (!preg_match('/.+\@.+\..+/', $email)) {
            // return error with email
            $this->addFlash('error', 'Please enter a valid email for your participant and try again.');

            return $redirect_response;
        }

        $person_factory = $this->get('person_factory');
        $context        = new CreatePersonContext('gateway.person');
        if ($person = $person_factory->getOrCreatePersonByEmail($email, $context)) {
            // only set the name if this email doesn't have a name (a new person)
            // otherwise anyone can CC a person and change their name in the system...
            if ($name && !$person->first_name) {
                $person->name = $name;
            }

            if ($ticket->hasParticipantPerson($person)) {
                $this->addFlash('success', 'The person you tried to add as a participant is already a participant on this ticket.');

                return $redirect_response;
            }

            $participant = new TicketParticipant();
            $participant->setPerson($person);
            $ticket->addParticipant($participant);
            $this->getEm()->persist($participant);
            $this->getEm()->flush($ticket);

            // return success
            $this->addFlash('success', sprintf('We have added %s (%s) as a participant to this ticket.', $person->getDisplayNameUser(), $person->getPrimaryEmailAddress()));

            return $redirect_response;
        }

        // return general error
        $this->addFlash('error', 'There was a problem when trying to add your participant. Please try again.');

        return $redirect_response;
    }

    /**
     * @Route("/tickets/{ticket_ref}/remove-cc/{cc_id}", name="portal_tickets_cc_remove")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     * @AutoPostOnGetRequest()
     */
    public function removeCcAction(Request $request, $ticket_ref, $cc_id)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $redirect_response = $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $ticket_ref]);

        $participant = $this->getEm()->getRepository('DeskPRO:TicketParticipant')->find($cc_id);
        $cc_person   = $participant->getPerson();

        // the passed participant must be a participant on the passed ticket ref
        if ($participant->getTicket() === $ticket) {
            $ticket->removeParticipantPerson($cc_person);
            $this->getEm()->flush();
            $this->addFlash('success', sprintf('We have removed %s (%s) as a participant to this ticket.', $cc_person->getDisplayNameUser(), $cc_person->getPrimaryEmailAddress()));

            return $redirect_response;
        }

        // return general error, likely the participant id and ticket id are not the same, which would be bad!
        $this->addFlash('error', 'There was a problem when trying to remove your participant. Please try again.');

        return $redirect_response;
    }

    /**
     * This URL is accessible if you know the ticket auth code. No other security is done here.
     *
     * VIEW ONLY. Must login to interact with things (which will redirect you to viewAction above).
     *
     * @Route("/ticket-view/{auth}", name="portal_tickets_guest_view")
     */
    public function viewGuestAction(Ticket $ticket, Request $request)
    {
        if (
            $this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)
            && $this->isGranted('USE_TICKETS')
            && $this->isGranted('ROLE_USER')
        ) {
            // the user passes all security requirements to view the normal ticket view page.
            // Redirect them to there.
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        if ($ticket->hasNotesOnly()) {
            throw new NotFoundHttpException(sprintf('ticket with auth "%s" found but has only agent notes', $ticket->getAuth()));
        }

        $ticket_view = $this->getTicketsViewService()->getUserTicketView($ticket);

        $timeline = $this->get('data.ticket_timeline')->getUserTimeline($ticket);

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketView($ticket);

        // TODO: if a user is logged in, we might want to allow SOME interaction on the ticket here...

        return $this->renderThemeView(
            'Theme:Tickets:guest-view.html.twig',
            array(
                'ticket'      => $ticket,
                'ticket_view' => $ticket_view,
                'timeline'    => $timeline,
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->tickets($ticket),
            )
        );
    }

    /**
     * @Route("/ticket-rate/{ticket_ref}/{auth}/{message_id}", name="portal_tickets_feedback", defaults={"message_id"=null})
     * @Route("/ticket-rate/{ticket_ref}/{auth}/{message_id}", name="user_tickets_feedback", defaults={"message_id"=null})
     */
    public function rateTicketAction(Request $request, $ticket_ref, $auth, $message_id = null)
    {
        if (!$ticket = $this->getTicketByRef($ticket_ref)) {
            throw new NotFoundHttpException('ref not found');
        }

        if ((string) $ticket->getAuth() !== (string) $auth) {
            throw new NotFoundHttpException('auth does not match ticket');
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $ticket_message_repo */
        $ticket_message_repo = $this->getRepo('DeskPRO:TicketMessage');
        if ($message_id) {
            /** @var \Application\DeskPRO\Entity\TicketMessage $message */
            $message = $ticket_message_repo->find($message_id);
        } else {
            /** @var \Application\DeskPRO\Entity\TicketMessage $message */
            $message = $ticket_message_repo->getLastAgentReply($ticket);
        }

        // message must exist and belong to the ticket requested
        if (!$message || $message->getTicketId() !== $ticket->getId()) {
            throw new NotFoundHttpException('message does not belong to ticket');
        }

        // message must not be an agent note and the person on the message must be an agent
        if ($message->is_agent_note || !$message->getPerson()->isAgent()) {
            throw new NotFoundHttpException('message cannot be an agent note or a non-agent message');
        }

        $person = $this->getAuthenticatedUserOrTicketPerson($ticket);

        /** @var \Application\DeskPRO\EntityRepository\TicketFeedback $ticket_feedback_repo */
        $ticket_feedback_repo = $this->getRepo('DeskPRO:TicketFeedback');
        $feedback             = $ticket_feedback_repo->getFeedback($message, $person, true);

        $rating    = null;
        $setrating = false;
        if ($request->get('rating', false)) {
            $rating = $request->get('rating');
        } elseif ($request->get('setrating', false)) {
            $rating    = $request->get('rating');
            $setrating = true;
        }

        if ($rating !== null) {
            $feedback->setRating($rating);

            if ($request->get('save')) {
                $last_message_id = $this->getConn()->fetchColumn('
                    SELECT message_id FROM ticket_feedback
                    WHERE ticket_id = ?
                    ORDER BY message_id DESC
                    LIMIT 1
                ', array($ticket->getId()));

                if (!$last_message_id || $message->getId() >= $last_message_id) {
                    $ticket->feedback_rating = $feedback->getRating();
                    $this->getEm()->persist($ticket);
                }

                $this->getEm()->persist($feedback);
                $this->getEm()->flush();

                $GLOBALS['DP_SET_SKIP_CACHE'] = true;

                // AJAX request used to auto-save rating as soon as user clicked link
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => true]);
                }
            }
        }

        return $this->renderThemeView('Theme:Tickets:feedback.html.twig', array(
            'ticket'    => $ticket,
            'message'   => $message,
            'feedback'  => $feedback,
            'setrating' => $setrating,
        ));
    }

    /**
     * @Route("/tickets/{ticket_ref}/unresolve", name="portal_tickets_unresolve")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     * @AutoPostOnGetRequest()
     */
    public function unresolveTicketAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $person = $this->getUser();

        if (!$ticket->isResolved()) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        // user permission to re-open ticket
        $permissions_bag = $this->getPermissionBag($person);
        if (!$permissions_bag->hasPermission('tickets.reopen_resolved')) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        $ticket->setStatus(Ticket::STATUS_AWAITING_AGENT);
        $this->saveEditedTicket($ticket, $person);
        $this->addFlash('success', $this->phrase('portal.flashes.ticket_re_opened'));

        return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
    }

    /**
     * ticket_ref can either be an ID or a ref depending on settings.
     *
     * @param $ticket_ref
     *
     * @return Ticket|null
     */
    protected function getTicketByRefOrId($ticket_ref)
    {
        if ($this->getBrandSetting('core.tickets.use_ref')) {
            if (!$ticket = $this->getTicketByRef($ticket_ref)) {
                if ($ticket = $this->getTicketById($ticket_ref)) {
                    if ($ticket->getPersonId() === $this->getCurrentPerson()->getId()) {
                        // we allow the "other" id to be used only if its the currently logged in user's own ticket
                        throw new RedirectToUrlException($this->generateUrl('portal_tickets_view', ['ticket_ref' => $ticket->getRef()]));
                    } else {
                        throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
                    }
                }
            }
        } else {
            if (!$ticket = $this->getTicketById($ticket_ref)) {
                if ($ticket = $this->getTicketByRef($ticket_ref)) {
                    if ($ticket->getPersonId() === $this->getCurrentPerson()->getId()) {
                        // we allow the "other" id to be used only if its the currently logged in user's own ticket
                        throw new RedirectToUrlException($this->generateUrl('portal_tickets_view', ['ticket_ref' => $ticket->getId()]));
                    } else {
                        throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
                    }
                }
            }
        }

        if (!$ticket) {
            throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
        }

        if ($ticket->hasNotesOnly()) {
            throw new NotFoundHttpException(sprintf('ticket with ref or id "%s" found but has only agent notes', $ticket_ref));
        }

        return $ticket;
    }

    /**
     * @param $ticket_ref
     *
     * @return Ticket
     */
    protected function getTicketByRef($ticket_ref)
    {
        $repo = $this->getRepo('DeskPRO:Ticket');

        return $repo->findOneBy(array('ref' => $ticket_ref));
    }

    /**
     * @param $ticket_ref
     *
     * @return Ticket
     */
    protected function getTicketById($ticket_ref)
    {
        $repo = $this->getRepo('DeskPRO:Ticket');

        return $repo->findOneBy(array('id' => $ticket_ref));
    }

    private function saveEditedTicket(Ticket $ticket, Person $person, $event_type = TicketTrigger::EVENT_TYPE_UPDATE)
    {
        $em = $this->getEm();

        $em->beginTransaction();

        try {
            $em->persist($ticket);

            $ticket_manager = $this->getTicketManager();
            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $ticket->disableAutoTicketProcess();
            $context = $ticket_manager->createUserExecutorContext($person, $event_type, 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $this->get('tickets.custom_per_field_manager')->flushDataQueue();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $ticket;
    }

    private function saveNewReply(Ticket $ticket, TicketMessage $message, $event_type = TicketTrigger::EVENT_TYPE_NEWREPLY)
    {
        $person = $message->person;

        $em = $this->getEm();

        $em->beginTransaction();

        try {
            foreach ($message->getAttachments() as $attachment) {
                if ($blob = $attachment->getBlob()) {
                    $blob->is_temp = false;
                }
                $attachment->setPerson($person);
            }

            $ticket->addMessage($message);

            // If status is pending, we'll switch it to open so agents will see it
            if (in_array(
                $ticket->getStatusCode(),
                array(
                    Ticket::STATUS_AWAITING_USER,
                    Ticket::STATUS_RESOLVED,
                )
            )) {
                $ticket->setStatus(Ticket::STATUS_AWAITING_AGENT);
            }

            if ($person->getId() && !$ticket->hasParticipantPerson($person)) {
                // someone like the org manager replying - need to make sure they're CC'd
                $ticket->addParticipantPerson($person);
            }

            $em->persist($ticket);
            $em->persist($message);

            $ticket_manager = $this->getTicketManager();
            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $ticket->disableAutoTicketProcess();
            $context = $ticket_manager->createUserExecutorContext($person, $event_type, 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $this->get('tickets.custom_per_field_manager')->flushDataQueue();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $ticket;
    }

    /**
     * @param $ticket
     *
     * @return array
     */
    protected function getRecentTimes($ticket)
    {
        $last_user_reply_in_seconds = null;
        if ($last_reply = $ticket->date_last_user_reply) {
            $last_reply                 = Carbon::createFromTimestamp($last_reply->getTimestamp());
            $last_user_reply_in_seconds = $last_reply->diffInSeconds();
        }

        $created            = Carbon::createFromTimestamp($ticket->date_created->getTimestamp());
        $created_in_seconds = $created->diffInSeconds();

        return array($last_user_reply_in_seconds, $created_in_seconds);
    }

    /**
     * If the user is authenticated use that person, else use the ticket person.
     *
     * @param Ticket $ticket
     *
     * @return Person
     */
    private function getAuthenticatedUserOrTicketPerson(Ticket $ticket)
    {
        $current_person = $this->getCurrentPerson();
        if (!$current_person instanceof PersonGuest) {
            return $current_person;
        }

        return $ticket->person;
    }
}
