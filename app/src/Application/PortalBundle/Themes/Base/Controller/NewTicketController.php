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
use Application\DeskPRO\People\PersonGuest;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class NewTicketController extends AbstractController
{
    public function newTicketAction(Request $request)
    {
        $person = $this->getUser() ?: new PersonGuest();

        $ticket = new Ticket();
        $ticket->person = $person;


        $form = $this->createForm(
            'deskpro_ticket',
            $ticket,
            array(
                'person' => $person
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            // we will do a lot of work here, and I definitely want to use services to do this work
            // (hopefully inside of domain events) so that we can easily see and extend what happens during ticket
            // creation instead of hacking one big function

//            (some of) the old code:
//            $newTicket = new NewTicket(Ticket::CREATED_WEB_PERSON_PORTAL, $person, $ticket);
//            $newTicket->setPersonContext($person); // have to set twice?
//            $newTicket->save();

            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->persist($person);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'created.ticket.phrase.here');

            return $this->redirect($this->generateUrl('portal_index'));

        }

        return $this->render('Theme:NewTicket:new_ticket.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
