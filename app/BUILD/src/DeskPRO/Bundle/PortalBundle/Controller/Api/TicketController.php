<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebFullType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebType;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketController.
 */
class TicketController extends AbstractApiController
{
    /**
     * @Route("/portal/api/tickets/display.js", name="portal_api_ticket_display")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function ticketDisplayAction()
    {
        $layouts = $this->getContainer()->getTicketLayoutManager()->getUserLayouts(true);
        $output  = 'window.DESKPRO_TICKET_DISPLAY ='.$layouts->compileJsObj();

        return new Response($output, Response::HTTP_OK, [
            'Content-Type' => 'text/javascript',
        ]);
    }

    /**
     * @Route("/portal/api/tickets/new", name="portal_api_ticket_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param string  $visitor_id
     *
     * @return View
     */
    public function newTicketAction(Request $request, $visitor_id)
    {
        if (!$this->isGranted(UseSectionVoter::USE_TICKETS)) {
            $can_open_ticket = $this->isGranted(UseSectionVoter::VIEW_TICKETS_LINK);
            $params          = [
                'can_open_ticket' => $can_open_ticket,
            ];
            $content = [
                'data' => $this->render('Theme:NewTicket:guest_new_ticket_not_allowed.html.twig', $params)
                    ->getContent(),
            ];

            return new View($content, Response::HTTP_OK);
        }
        $ticket_service = $this->get('tickets.new_ticket');
        $ticket         = $ticket_service->createNewTicket($request, $visitor_id, $this->getUser());
        $person         = $ticket->getPerson();
        $ticket_message = $ticket->messages[0];

        $form = $this->createForm(TicketWithLayoutsWebType::class, $ticket, [
            'person'                        => $person,
            'action'                        => $this->generateUrl('portal_api_ticket_new'),
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
            'allow_extra_fields'            => true,
            'use_captcha'                   => false,
            'department_id'                 => $request->query->getInt('department_id'),
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $email  = $person->getPrimaryEmail();
            $person = $this->get('data.person')->getPersonForEmail($email->getEmail());

            if ($person) {
                $ticket->setPerson($person);
                $ticket_message->setPerson($person);
                foreach ($ticket_message->getAttachments() as $attachment) {
                    $attachment->setPerson($person);
                }

                $ticket_service->acceptNewTicket($ticket, $request);
            } else {
                $ticket_service->acceptNewTicketForGuest($ticket, $request);
            }

            return new View();
        }

        $form_full = $this->createForm(TicketWithLayoutsWebFullType::class, $ticket, [
            'person'      => $person,
            'action'      => $this->generateUrl('portal_api_ticket_new'),
            'use_captcha' => false,
        ]);

        $params = [
            'form'                    => $form->createView(),
            'form_full'               => $form_full->createView(),
            'form_errors'             => $form->isSubmitted() ? $form->getErrors() : [],
            'show_ticket_suggestions' => (bool) $this->getBrandContainer()->getSetting('core.show_ticket_suggestions'),
        ];

        $content     = ['data' => $this->render('Theme:NewTicket:new_ticket_form.html.twig', $params)->getContent()];
        $status_code = !$form->isSubmitted() ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return new View($content, $status_code);
    }
}
