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
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class NewTicketController extends AbstractController
{
    /**
     * @Route("/new-ticket", name="portal_new_ticket")
     * @Route("/new-ticket", name="user_tickets_new")
     * @Security("is_granted('USE_TICKETS')")
     * @PageHttpCache()
     */
    public function newTicketAction(Request $request, $visitor_id)
    {
        $person = $this->getUser() ?: new PersonGuest();

        $ticket         = $this->getTicketManager()->createTicket();
        $ticket_message = new TicketMessage();
        $ticket_message->setVisitorId($visitor_id);
        $ticket_message->setIpAddress($request->getClientIp());
        $ticket->setPerson($person);
        $ticket_message->setPerson($person);
        $ticket->addMessage($ticket_message);

        // do a one through with the GET request to update our model before starting the "real" form
        $form = $this->createForm('ticket', $ticket, array(
            'person'            => $person,
            'ticket_message'    => $ticket_message,
            'method'            => 'GET',
            'validation_groups' => false,
            'settings'          => $this->getBrandContainer()->getSettings(),
            'action'            => $this->generateUrl('portal_new_ticket'),
        ));
        $form->submit($request->query->get('ticket', array()), false);

        foreach ($ticket_message->getAttachments() as $attachment) {
            if (!$attachment->getBlob()) {
                $ticket_message->removeAttachment($attachment);
            }
        }

        $form = $this->createForm('ticket', $ticket, array(
            'person'         => $person,
            'ticket_message' => $ticket_message,
            'settings'       => $this->getBrandContainer()->getSettings(),
            'action'         => $this->generateUrl('portal_new_ticket'),
            'attr'           => ['data-save-draft' => 'new_ticket'],
        ));
        $form->handleRequest($request);

        $rerendering = false;
        if ($form->has('rerender_form')) {
            $rerendering = true;
        }
        $rerendering_saved = $request->attributes->get('rerender-form', false);

        if ($form->isValid()) {
            // dont process if user hit "more attachments"
            if ($form->getClickedButton()->getConfig()->getName() !== 'more_attachments') {
                // if the form set a hidden field "rerender_form" then we want to skip actual processing for now
                // keep the $form->has('rerender_form') because it may have changed after $form->isValid
                if (!$form->has('rerender_form') && !$rerendering_saved) {
                    // deal with guests via negotiating with PersonFactory
                    if ($person instanceof PersonGuest) {
                        try {
                            $person = $this->getPersonFactory()->createPersonFromGuest($person);
                        } catch (LoginRequiredException $e) {
                            // the email used belongs to a user, and brand settings say they need to log in
                            $person = $e->getPerson();

                            return $this->getFormSaver()->saveFormForPerson($person, $form, $request);
                        }

                        // since the guest is set on the form, we need to update all of the associations
                        $ticket->setPerson($person);
                        $ticket_message->setPerson($person);
                        foreach ($ticket_message->getAttachments() as $attachment) {
                            $attachment->setPerson($person);
                        }
                    }

                    $this->submitNewTicketAbuseCheck($person, $request->getClientIp());

                    $ticket = $this->saveNewTicket($ticket, $person);

                    $this->addFlash('success', $this->phrase('portal.flashes.ticket_created'));

                    if (!$person->isUser()) { // not a user, redirect to thank you
                        $this->get('portal_email_sender')->sendNewTicketGuestThankYou($ticket);

                        return $this->redirectToRoute('portal_new_ticket_guest_thank_you');
                    }

                    // is a user, redirect to ticket view (will ask to login if not already)
                    return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
                }
            }
        } elseif ($form->isSubmitted()) {
            $this->submitNewTicketAbuseCheck($person, $request->getClientIp());
        }

        $form_full = $this->createForm('ticket', $ticket, array(
            'person'         => $person,
            'ticket_message' => null,
            'settings'       => $this->getBrandContainer()->getSettings(),
            'full_version'   => true,
            'action'         => $this->generateUrl('portal_new_ticket'),
        ));

        /** @var \Application\DeskPRO\TicketLayout\LayoutCollection $layouts */
        $layouts           = $this->container->getTicketLayoutManager()->getUserLayouts(true);
        $ticket_display_js = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewTicket();

        return $this->renderThemeView(
            'Theme:NewTicket:new_ticket.html.twig', array(
                'form'              => $form->createView(),
                'form_full'         => $form_full->createView(),
                'ticket_display_js' => $ticket_display_js,
                'rerendering'       => $rerendering,
                'rerendering_saved' => $rerendering_saved,
                'breadcrumbs'       => $breadcrumbs,
                'page_title'        => $this->createPageTitle()->newticket(),
                //'form_errors'       => $form->isSubmitted() ? $form->getErrors(true, true) : []
            )
        );
    }

    protected function submitNewTicketAbuseCheck($person, $ip)
    {
        $check = new SubmitTicketAbuseCheck($person, $ip);
        $this->getAntiAbuseService()->check($check);
    }

    /**
     * @Route("/new-ticket/thank-you", name="portal_new_ticket_guest_thank_you")
     * @Security("is_granted('USE_TICKETS')")
     * @PageHttpCache()
     */
    public function guestThankYouAction()
    {
        return $this->renderThemeView(
            'Theme:NewTicket:guest_thank_you.html.twig', array(
                'breadcrumbs' => $this->getBreadcrumbGenerator()->buildNewTicketGuestThankYou(),
                'page_title'  => $this->createPageTitle()->newticketGuestThankYou(),
            )
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Ticket
     */
    protected function getTicketsRepo()
    {
        return $this->getRepo('DeskPRO:Ticket');
    }

    private function saveNewTicket(Ticket $ticket, Person $person)
    {
        $em = $this->getEm();

        $em->beginTransaction();

        try {
            $em->persist($ticket);

            $ticket_manager = $this->getTicketManager();
            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $ticket->disableAutoTicketProcess();
            $context = $ticket_manager->createUserExecutorContext($person, 'newticket', 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $this->get('tickets.custom_per_field_manager')->flushDataQueue();
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
