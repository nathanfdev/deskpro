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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Model\TicketFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TicketsController extends AbstractController
{
    /**
     * @Route("/tickets/{type}", name="portal_tickets", defaults={"type":"own"}, requirements={"type":"organization"})
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     */
    public function indexAction(Request $request, $type)
    {
        $person = $this->getUser();
        $per_page = 2; // TODO: brand setting?

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
        $awaiting_user_pg_param = 'user_page';
        $awaiting_user_pg = $request->query->get($awaiting_user_pg_param, 1);
        $awaiting_agent_pg_param = 'agent_page';
        $awaiting_agent_pg = $request->query->get($awaiting_agent_pg_param, 1);
        $resolved_pg_param = 'resolved_page';
        $resolved_pg = $request->query->get($resolved_pg_param, 1);

        // fetch data
        $tds = $this->getTicketsDataService();
        $awaiting_user_pager = $tds->getPager($person, $awaiting_user_filter, $awaiting_user_pg, $per_page);
        $awaiting_agent_pager = $tds->getPager($person, $awaiting_agent_filter, $awaiting_agent_pg, $per_page, $awaiting_agent_pg_param);
        $resolved_pager = $tds->getPager($person, $resolved_filter, $resolved_pg, $per_page, $resolved_pg_param);

        return $this->renderThemeView(
            'Theme:Tickets:index.html.twig',
            array(
                'awaiting_user_tickets' => $awaiting_user_pager,
                'awaiting_user_tickets_pg_param' => $awaiting_user_pg_param,

                'awaiting_agent_tickets' => $awaiting_agent_pager,
                'awaiting_agent_tickets_pg_param' => $awaiting_agent_pg_param,

                'resolved_tickets' => $resolved_pager,
                'resolved_tickets_pg_param' => $resolved_pg_param,

                'type' => $type,
                'person' => $person
            )
        );
    }

    /**
     * @Route("/tickets/{id}", name="portal_tickets_view")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS') and is_granted('TICKET_VIEW', ticket)")
     */
    public function viewAction(Ticket $ticket, Request $request)
    {
        $form_data = array(
            'ticket_message' => $message = new TicketMessage(),
            'attachments' => new ArrayCollection()
        );

        $form = $this->createForm('ticket_reply', $form_data, array(
            'ticket' => $ticket,
            'ticket_message' => $message,
            'person' => $this->getUser(),
            'settings' => $this->getBrandContainer()->getSettings()
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton()->getConfig()->getName() !== "more_attachments") {
                // We don't continue here if they just clicked the "add more attachments" button

                // TODO: fire an event (Ticket::ADD_MESSAGE)
                $this->getRepo('DeskPRO:Ticket')->saveNewMessage($ticket, $message);

                $this->addFlash('success', 'ticket.successful_new_reply.translated');

                return $this->redirectToRoute('portal_tickets_view', array('id' => $ticket->getId()));
            }
        }

        $ticket_view = $this->getTicketsViewService()->getUserTicketView($ticket);

        return $this->renderThemeView(
            'Theme:Tickets:view.html.twig',
            array(
                'ticket_view' => $ticket_view,
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/tickets/{id}/edit", name="portal_tickets_edit")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS') and is_granted('TICKET_EDIT', ticket)")
     */
    public function editAction(Ticket $ticket, Request $request)
    {
        $form = $this->createForm('ticket', $ticket, array(
            'person' => $this->getUser(),
            'ticket_visibility' => 'edit',
            'settings' => $this->getBrandContainer()->getSettings()
        ));

        $rerendering = false;
        if ($form->has('rerender_form')) {
            $rerendering = true;
        }

        $form->handleRequest($request);

        if ($form->isValid()) {

            // if the form set a hidden field "rerender_form" then we want to skip actual processing for now
            if (!$form->has('rerender_form')) {
                // TODO: fire an event (Ticket::EDIT)
                $this->getRepo('DeskPRO:Ticket')->saveTicket($ticket);

                $this->addFlash('success', 'updated.ticket.translated');

                return $this->redirectToRoute('portal_tickets_view', array('id' => $ticket->getId()));
            }

        }

        return $this->renderThemeView(
            'Theme:Tickets:edit.html.twig',
            array(
                'ticket' => $ticket,
                'form' => $form->createView(),
                'rerendering' => $rerendering
            )
        );
    }
}
