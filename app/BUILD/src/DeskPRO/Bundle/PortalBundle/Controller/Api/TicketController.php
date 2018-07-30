<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebFullType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebType;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\UseSectionVoter;
use DeskPRO\Bundle\PortalBundle\Ticket\NewTicket;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
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
            $params = [
                'can_open_ticket' => $this->isGranted(UseSectionVoter::VIEW_TICKETS_LINK),
            ];

            return new View([
                'data' => $this->render('Theme:NewTicket:guest_new_ticket_not_allowed.html.twig', $params)->getContent(),
            ]);
        }

        /* @var NewTicket $ticket_service */
        $ticketService = $this->get('tickets.new_ticket');
        $ticket        = $ticketService->createNewTicket(
            $request,
            $visitor_id,
            $this->getUser(),
            $this->getBrandContainer()->getBrand(),
            Ticket::CREATED_WEB_PERSON_WIDGET
        );

        $person = $ticket->getPerson();

        $formOptions = [
            'person'                        => $person,
            'action'                        => $this->generateUrl('portal_api_ticket_new'),
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
            'allow_extra_fields'            => true,
            'use_captcha'                   => false,
            'department_id'                 => $request->query->getInt('department_id'),
            'hide_department_field'         => $request->query->getBoolean('hide_department_field'),
            'subject_type'                  => $request->get('subject_type'),
            'default_subject'               => $request->get('subject'),
            'ticket_view_context'           => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'             => TicketWithLayoutsContext::VISIBILITY_NEW,
            'form_type'                     => $request->get('type'),
        ];

        if ($request->isMethod('get')) {
            $formOptions['validation_groups']  = false;
            $formOptions['allow_extra_fields'] = true;
        }

        $form = $this->createForm(TicketWithLayoutsWebType::class, $ticket, $formOptions);
        $form->handleRequest($request);

        // set default values on the main form
        if ($request->isMethod('get') && $request->query->get('ticket') && !$form->isSubmitted()) {
            $defaultData = $request->query->get('ticket') ?: [];
            if ($request->query->getInt('department_id') && !isset($defaultData['department'])) {
                $defaultData['department'] = $request->query->getInt('department_id');
            }
            if ($request->query->getInt('subject') && !isset($defaultData['subject'])) {
                $defaultData['subject'] = $request->query->getInt('subject');
            }

            $form->submit($defaultData);
            FormValidatorChecker::clearFormErrors($form);
        }

        if ($request->isMethod('post') && $form->isValid()) {
            $email     = $person->getPrimaryEmail();
            $person    = $this->get('data.person')->getPersonForEmail($email->getEmail());
            $guestForm = $this->createForm(TicketWithLayoutsWebType::class, $ticket, $formOptions);

            if ($person) {
                $ticket->setPerson($person);
                $guestForm->handleRequest($request);

                if (!$this->getUser() || $this->getUser() instanceof PersonGuest) {
                    // if the user is not authorized then don't allow to change person entity
                    $this->getManager()->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($ticket->getPerson()));
                }

                $ticketService->acceptNewTicket($ticket, $request, 'widget');
            } else {
                $ticketService->acceptNewTicketForGuest($ticket, $request, $guestForm, 'widget');
            }

            // check if ticket was created and then return success response
            if ($ticket->getId()) {
                return new View(['ticket_id' => $ticket->getId()]);
            } else {
                $form->addError(new FormError('Unable to save ticket.'));
            }
        }

        $formFull = $this->createForm(TicketWithLayoutsWebFullType::class, null, [
            'action'              => $this->generateUrl('portal_api_ticket_new'),
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_NEW,
        ]);

        // set default values on the full form
        if ($request->isMethod('get') && $request->query->get('ticket')) {
            $formFull->submit($request->query->get('ticket') ?: []);
            FormValidatorChecker::clearFormErrors($formFull);
        }

        $params = [
            'form'                    => $form->createView(),
            'form_full'               => $formFull->createView(),
            'form_errors'             => $form->isSubmitted() ? $form->getErrors() : [],
            'show_ticket_suggestions' => (bool) $this->getBrandContainer()->getSetting('core.show_ticket_suggestions'),
        ];

        $content    = ['data' => $this->render('Theme:NewTicket:new_ticket_form.html.twig', $params)->getContent()];
        $statusCode = $request->isMethod('get') || $form->isValid() ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return new View($content, $statusCode);
    }
}
