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

namespace Application\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TicketsController extends AbstractController
{
    public function indexAction()
    {
        // TODO: we just grab them all for now, without filtering...
        $tickets = $this->getEm()
            ->getRepository('DeskPRO:Ticket')
            ->findBy(
                array(
                    'person' => $this->getUser()
                )
            );

        return $this->render('Theme:Tickets:index.html.twig', array(
            'tickets' => $tickets
        ));
    }

    /**
     * TODO: put a Security annotation to make sure user is granted acess to VIEW this ticket (being granted acces to
     * VIEW this ticket implies you are logged in, becasue the voter denies non logged in users, so we dont need to
     * make multiple security assertions, see what I mean?)
     */
    public function viewAction(Ticket $ticket, Request $request)
    {
        $message = new TicketMessage();

        $form = $this->createForm('deskpro_ticket_message', $message, array(
            'ticket'        => $ticket,
            'message_label' => 'Reply',
            'person'        => $this->getUser()
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            // TODO: fire an event (Ticket::ADD_MESSAGE)
            // TODO: Make the Ticket repository do this actual persisting logic
            $em = $this->getEm();
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'ticket.successful_new_reply.translated');

            return $this->redirectToRoute('portal_tickets_view', array('id' => $ticket->getId()));
        }

        return $this->render('Theme:Tickets:view.html.twig', array(
            'ticket' => $ticket,
            'form'   => $form->createView()
        ));
    }

    /**
     * TODO: put a Security annotation to make sure user is granted acess to EDIT this ticket (being granted acces to
     * EDIT this ticket implies you are logged in, becasue the voter denies non logged in users, so we dont need to
     * make multiple security assertions, see what I mean?)
     */
    public function editAction(Ticket $ticket, Request $request)
    {
        $form = $this->createForm('deskpro_ticket', $ticket, array(
            'person'            => $this->getUser(),
            'ticket_visibility' => 'edit'
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            // TODO: fire an event (Ticket::EDIT)
            // TODO: have the Ticket repo do this persistence logic
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();

            $this->addFlash('success', 'updated.ticket.translated');

            return $this->redirectToRoute('portal_tickets_view', array('id' => $ticket->getId()));
        }

        return $this->render('Theme:Tickets:edit.html.twig', array(
                'ticket' => $ticket,
                'form'   => $form->createView())
        );
    }
}
