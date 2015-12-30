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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\TicketMessage;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketController.
 */
class TicketController extends AbstractApiController
{
    /**
     * @Route("/portal/api/tickets/new", name="portal_api_ticket_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function newTicketAction(Request $request)
    {
        $person = $this->getUser() ?: $this->getDoctrine()->getRepository('DeskPRO:Person')->find(1);
        $ticket = $this->get('ticket_manager')->createTicket();

        $ticket_message = new TicketMessage();
        $ticket_message->setIpAddress($request->getClientIp());
        $ticket->setPerson($person);
        $ticket_message->setPerson($person);
        $ticket->addMessage($ticket_message);
        $lang = $this->get('language_manager')->getLanguageStack()->getActiveOrDefault();
        $ticket->setLanguage($lang);

        $form = $this->createForm('ticket', $ticket, [
            'person'                        => $person,
            'ticket_message'                => $ticket_message,
            'settings'                      => $this->getBrandContainer()->getSettings(),
            'action'                        => $this->generateUrl('portal_new_ticket'),
            'attr'                          => ['data-save-draft' => 'new_ticket'],
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
            'allow_extra_fields'            => true,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
        }

        $form_full = $this->createForm('ticket', $ticket, [
            'person'         => $person,
            'ticket_message' => null,
            'settings'       => $this->getBrandContainer()->getSettings(),
            'full_version'   => true,
            'action'         => $this->generateUrl('portal_new_ticket'),
        ]);

        // show ticket deflection? (suggestions)
        $show_ticket_suggestions = (bool) $this->getBrandContainer()->getSetting('core.show_ticket_suggestions');

        return new View([
            'data' => $this->render('Theme:NewTicket:new_ticket_form.html.twig', [
                'form'                    => $form->createView(),
                'form_full'               => $form_full->createView(),
                'form_errors'             => $form->isSubmitted() ? $form->getErrors() : [],
                'show_ticket_suggestions' => $show_ticket_suggestions,
            ])->getContent(),
        ], !$form->isSubmitted() || $form->isValid() ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    protected function getBrandContainer()
    {
        return $this->get('brand_stack')->getActive();
    }
}
