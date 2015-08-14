<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class TicketsController extends AbstractController
{
    /**
     * @Route("/tickets/{type}", name="portal_tickets", defaults={"type":"own"}, requirements={"type":"organization"})
     * @Route("/tickets", name="user_tickets")
     * @Route("/tickets/organization", name="user_tickets_organization", defaults={"type":"organization"})
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function indexAction(Request $request, $type)
    {
        $person   = $this->getUser();
        $per_page = $this->getBrandSetting('portal.per_page_tickets');

        // access to organization list?
        if ($type === 'organization' && !($person->organization && $person->organization_manager)) {
            return $this->redirectToRoute('portal_tickets');
        }

        // create data service filters
        $awaiting_user_filter = new TicketFilter(
            $type,
            TicketFilter::CATEGORY_AWAITING_USER,
            $request->query->get('user_sort', 'activity'),
            $request->query->get('user_direction', 'desc')
        );
        $awaiting_agent_filter = new TicketFilter(
            $type,
            TicketFilter::CATEGORY_AWAITING_AGENT,
            $request->query->get('agent_sort', 'activity'),
            $request->query->get('agent_direction', 'desc')
        );
        $resolved_filter = new TicketFilter(
            $type,
            TicketFilter::CATEGORY_RESOLVED,
            $request->query->get('resolved_sort', 'activity'),
            $request->query->get('resolved_direction', 'desc')
        );

        // page
        $awaiting_user_pg_param  = 'user_page';
        $awaiting_user_pg        = $request->query->get($awaiting_user_pg_param, 1);
        $awaiting_agent_pg_param = 'agent_page';
        $awaiting_agent_pg       = $request->query->get($awaiting_agent_pg_param, 1);
        $resolved_pg_param       = 'resolved_page';
        $resolved_pg             = $request->query->get($resolved_pg_param, 1);

        // fetch data
        $tds                  = $this->getTicketsDataService();
        $awaiting_user_pager  = $tds->getPager($person, $awaiting_user_filter, $awaiting_user_pg, $per_page);
        $awaiting_agent_pager = $tds->getPager($person, $awaiting_agent_filter, $awaiting_agent_pg, $per_page);
        $resolved_pager       = $tds->getPager($person, $resolved_filter, $resolved_pg, $per_page);

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketList();

        return $this->renderThemeView(
            'Theme:Tickets:index.html.twig',
            array(
                'awaiting_user_tickets'          => $awaiting_user_pager,
                'awaiting_user_tickets_pg_param' => $awaiting_user_pg_param,

                'awaiting_agent_tickets'          => $awaiting_agent_pager,
                'awaiting_agent_tickets_pg_param' => $awaiting_agent_pg_param,

                'resolved_tickets'          => $resolved_pager,
                'resolved_tickets_pg_param' => $resolved_pg_param,

                'type'   => $type,
                'person' => $person,
                'breadcrumbs' => $breadcrumbs,
                'page_title' => $this->createPageTitle()->tickets()
            )
        );
    }

    /**
     * @Route("/tickets/{ticket_ref}", name="portal_tickets_view")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function viewAction(Request $request, $ticket_ref)
    {
        $ticket = $this->getTicketByRefOrId($ticket_ref);

        // TODO: security TICKET_VIEW

        $form_data = array(
            'ticket_message' => $message = new TicketMessage(),
            'attachments'    => new ArrayCollection(),
        );

        $form = $this->createForm('ticket_reply', $form_data, array(
            'ticket'         => $ticket,
            'ticket_message' => $message,
            'person'         => $this->getUser(),
            'settings'       => $this->getBrandContainer()->getSettings(),
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton()->getConfig()->getName() !== "more_attachments") {
                // We don't continue here if they just clicked the "add more attachments" button

                // TODO: fire an event (Ticket::ADD_MESSAGE)
                $this->getRepo('DeskPRO:Ticket')->saveNewMessage($ticket, $message);

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
                'ticket'      => $ticket_view,
                'timeline'    => $timeline,
                'can_edit'    => $this->isGranted('TICKET_EDIT', $ticket),
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbs,
                'page_title' => $this->createPageTitle()->tickets($ticket)
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
            $this->isGranted('TICKET_VIEW', $ticket)
            && $this->isGranted('USE_TICKETS')
            && $this->isGranted('ROLE_USER')
        ) {
            // the user passes all security requirements to view the normal ticket view page. Redirect them to there.
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
                'ticket'      => $ticket_view,
                'timeline'    => $timeline,
                'breadcrumbs' => $breadcrumbs,
                'page_title' => $this->createPageTitle()->tickets($ticket)
            )
        );
    }

    /**
     * @Route("/tickets/{ticket_ref}/edit", name="portal_tickets_edit")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function editAction(Request $request, $ticket_ref)
    {
        $ticket = $this->getTicketByRefOrId($ticket_ref);

        // TODO: security TICKET_EDIT

        $form = $this->createForm('ticket', $ticket, array(
            'person'            => $this->getUser(),
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
                $this->getEm()->persist($ticket);
                $this->getEm()->flush($ticket);
                $this->get('tickets.custom_per_field_manager')->flushDataQueue();

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
                'page_title' => $this->createPageTitle()->tickets($ticket)
            )
        );
    }

    /**
     * ticket_ref can either be an ID or a ref depending on settings
     *
     * @param $ticket_ref
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
}
