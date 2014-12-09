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


use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class NewTicketController extends AbstractController
{
    /**
     * @Security("is_granted('USE_TICKETS')")
     */
    public function newTicketAction(Request $request)
    {
        $person = $this->getUser() ?: new PersonGuest();

        $ticket = $this->getTicketManager()->createTicket();
        $ticket_message = new TicketMessage();
        $ticket->setPerson($person);
        $ticket_message->setPerson($person);
        $ticket->addMessage($ticket_message);


        // do a one through with the GET request to update our model before starting the "real" form
        $form = $this->createForm('ticket', $ticket, array(
                'person' => $person,
                'ticket_message' => $ticket_message,
                'method' => 'GET',
                'validation_groups' => false
        ));
        $form->submit($request->get('ticket', array()), false);


        $form = $this->createForm('ticket', $ticket, array(
                'person' => $person,
                'ticket_message' => $ticket_message
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {


            // deal with guests via negotiating with PersonFactory
            if ($person instanceof PersonGuest) {
                $person = $this->getPersonFactory()->createPersonFromGuest($person);

                // since the guest is set on the form, we need to update all of the associations
                // TODO: we should be able to deal with this better by using a contact to beign with
                $ticket->setPerson($person);
                $ticket_message->setPerson($person);
                foreach ($ticket_message->getAttachments() as $attachment) {
                    $attachment->setPerson($person);
                }

            }
            
            $ticket = $this->saveNewTicket($ticket, $person);

            $this->addFlash('success', 'created.ticket.phrase.here');

            return $this->redirectToRoute('portal_tickets_view', array('id' => $ticket->getId()));
        }

        return $this->render('Theme:NewTicket:new_ticket.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Ticket
     */
    protected function getTicketsRepo()
    {
        return $this->getRepo('DeskPRO:Ticket');
    }

    /**
     * @return \Application\DeskPRO\Tickets\TicketManager
     */
    protected function getTicketManager()
    {
        return $this->get('ticket_manager');
    }

    /**
     * @return \Application\PersonBundle\Person\PersonFactory
     */
    protected function getPersonFactory()
    {
        return $this->get('person_factory');
    }

    private function saveNewTicket(Ticket $ticket, Person $person)
    {
        $em = $this->getEm();

        $em->beginTransaction();

        try {

            $em->persist($ticket);

            $ticket_manager = $this->getTicketManager();
            $context = $ticket_manager->createUserExecutorContext($person, 'newticket', 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $em->commit();

        } catch (DuplicateTicketException $e) {
           $em->rollback();
            $ticket = $em->find('DeskPRO:Ticket', $e->ticket_id);

            return $ticket;
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $ticket;
    }
}
