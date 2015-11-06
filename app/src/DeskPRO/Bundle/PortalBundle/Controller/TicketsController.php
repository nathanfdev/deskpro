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
use Application\DeskPRO\Entity\TicketTrigger;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTablesCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

        return $this->renderThemeView(
            'Theme:Tickets:view.html.twig',
            array(
                'ticket'      => $ticket,
                'ticket_view' => $ticket_view,
                'timeline'    => $timeline,
                'can_edit'    => $this->isGranted('TICKET_EDIT', $ticket),
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->tickets($ticket),
            )
        );
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

        return $this->renderThemeView(
            'Theme:Tickets:edit.html.twig',
            array(
                'ticket'      => $ticket,
                'form'        => $form->createView(),
                'rerendering' => $rerendering,
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->createPageTitle()->tickets($ticket),
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
        $repo = $this->getRepo('DeskPRO:Ticket');

        if ($this->getBrandSetting('core.tickets.use_ref')) {
            return $repo->findOneBy(array('ref' => $ticket_ref));
        }

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
}
