<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebFullType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebType;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\EmailValidationRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NewTicketController.
 */
class NewTicketController extends AbstractController
{
    /**
     * @Route("/new-ticket", name="portal_new_ticket")
     * @Route("/new-ticket", name="user_tickets_new")
     * @Route("/new-ticket/{department_id}", requirements={"department_id": "\d+"})
     *
     * @Security("is_granted('USE_TICKETS')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param string  $visitor_id
     *
     * @return RedirectResponse|Response
     */
    public function newTicketAction(Request $request, $visitor_id)
    {
        $ticket = $this->getNewTicketService()->createNewTicket(
            $request,
            $visitor_id,
            $this->getCurrentPerson(),
            $this->getBrandContainer()->getBrand(),
            Ticket::CREATED_WEB_PERSON_PORTAL
        );

        $person = $ticket->getPerson();

        $formOptions = [
            'ticket_view_context'   => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'     => TicketWithLayoutsContext::VISIBILITY_NEW,
            'person'                => $person,
            'action'                => $this->generateUrl('portal_new_ticket'),
            'saved_form_subrequest' => $this->isSavedFormSubRequest($request),

            // we can get pre-defined department id from the request
            // if we get it from query params then we should just pre-select the department option
            // if we get it from route path (/new-ticket/{department_id}) then we should make department field hidden
            'department_id'         => $request->attributes->getInt('department_id') ?: $request->query->getInt('department_id'),
            'hide_department_field' => (bool) $request->attributes->getInt('department_id'),
        ];

        if ($request->isMethod('get') && $request->query->count()) {
            // to set form default values from request query
            $formOptions['validation_groups']             = false;
            $formOptions['csrf_double_submit_skip_check'] = true;
        }

        $form = $this->createForm(TicketWithLayoutsWebType::class, $ticket, $formOptions);
        $form->handleRequest($request);

        $rerendering       = $form->has('rerender_form');
        $rerendering_saved = $request->attributes->get('rerender-form', false);

        if (!$form->isSubmitted() && $request->query->has('ticket')) {
            // set default values
            // using the string constant to acquire data from query instead of Form::getName for BC
            $form->submit($request->query->get('ticket') ?: []);
            FormValidatorChecker::clearFormErrors($form);
        }

        if ($form->isValid() && $request->isMethod('post')) {
            // dont process if user hit "more attachments"
            if (!$form->getClickedButton() || $form->getClickedButton()->getConfig()->getName() !== 'more_attachments') {
                if (!$rerendering && !$rerendering_saved) {
                    // deal with guests via negotiating with PersonFactory
                    if ($person instanceof PersonGuest) {
                        $guestForm = $this->createForm(TicketWithLayoutsWebType::class, $ticket, $formOptions);

                        try {
                            $this->getPersonFactory()->checkGuestForValidation($person, $this->isSavedFormSubRequest($request));
                            $newTicket = $this->getNewTicketService()->acceptNewTicketForGuest($ticket, $request, $guestForm, 'portal');

                            return $this->onSavedTicket($newTicket, $request);
                        } catch (\InvalidArgumentException $e) {
                            $this->addFlash('error', $this->phrase('portal.forms.error_email_required'));

                            return $this->redirectToRoute('portal_new_ticket');
                        } catch (LoginRequiredException $e) {
                            // the email used belongs to a user, and brand settings say they need to log in
                            $person = $e->getPerson();
                            if ($person instanceof PersonGuest) {
                                $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_TICKET, $form, $request, $person->getEmail(), $person->getDisplayName());

                                return new RedirectResponse(
                                    $this->container->get('router')->generate('portal_login', [
                                        'saved_form' => $savedForm->getExternalCode(),
                                    ])
                                );
                            } else {
                                return $this->getFormSaver()->saveFormForPersonLogin(SavedForm::TYPE_NEW_TICKET, $person, $form, $request);
                            }
                        } catch (EmailValidationRequiredException $e) {
                            // this exception just means the guest exists but does not
                            // have a valid email address
                            // we still need to check this setting
                            if ($this->getBrandSetting('core_tickets.web_require_validation')) {
                                $saved_form = $this->getFormSaver()->saveForm(
                                    SavedForm::TYPE_NEW_TICKET,
                                    $form,
                                    $request,
                                    $person->getEmailAddress(),
                                    $person->getDisplayName()
                                );

                                $this->get('portal_validation')->sendTicketVerificationEmail($ticket, $saved_form);
                                $this->addFlash('success', $this->phrase('portal.flashes.guest_new_ticket_must_verify'));

                                return $this->redirectToRoute('portal_thanks_verify');
                            } else {
                                // this is a guest that we are accepting
                                $newTicket = $this->getNewTicketService()->acceptNewTicketForGuest($ticket, $request, $guestForm, 'portal');

                                return $this->onSavedTicket($newTicket, $request);
                            }
                        }
                    } else {
                        $newTicket = $this->getNewTicketService()->acceptNewTicket($ticket, $request, 'portal');

                        return $this->onSavedTicket($newTicket, $request);
                    }
                }
            }
        } elseif ($this->getNewTicketService()->hasDupeError($form)) {
            /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
            $ticketRepo = $this->getRepo(Ticket::class);

            // if we got ticket then it was created less than 5 min ago, redirecting
            // otherwise the form returns duplicate error message
            $dupeTicket = $ticketRepo->checkDupeTicket($ticket, 5 * 60);
            if ($dupeTicket) {
                if ($person instanceof PersonGuest) {
                    return $this->redirect($this->generateUrl('portal_thanks', ['ticket_ref' => $dupeTicket->getRef()]));
                } else {
                    return $this->redirect($this->generateUrl('portal_tickets_view', ['ticket_ref' => $dupeTicket->getRef()]));
                }
            }
        }

        $formFull = $this->createForm(TicketWithLayoutsWebFullType::class, null, [
            'action'              => $this->generateUrl('portal_new_ticket'),
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_NEW,
        ]);

        // set default values on the full form
        if ($request->isMethod('get') && $request->query->get('ticket')) {
            $formFull->submit($request->query->get('ticket') ?: []);
            FormValidatorChecker::clearFormErrors($formFull);
        }

        /** @var \Application\DeskPRO\TicketLayout\LayoutCollection $layouts */
        $layouts           = $this->getContainer()->getTicketLayoutManager()->getUserLayouts(true);
        $ticket_display_js = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        // show ticket deflection? (suggestions)
        $show_ticket_suggestions = (bool) $this->getBrandSetting('core.show_ticket_suggestions');

        $formView     = $form->createView();
        $formFullView = $formFull->createView();

        if (isset($formView->children['captcha_captcha_auto_added'])) {
            $formFullView->children['captcha_captcha_auto_added'] = $formView->children['captcha_captcha_auto_added'];
        }

        $abuseCheck = new SubmitTicketAbuseCheck($person, $request->getClientIp());
        $abuseCheck->markAsCheckOnly();
        $this->getAntiAbuseService()->check($abuseCheck);

        return $this->renderThemeView(
            'Theme:NewTicket:new_ticket.html.twig',
            [
                'form'                    => $formView,
                'form_full'               => $formFullView,
                'ticket_display_js'       => $ticket_display_js,
                'rerendering'             => $rerendering,
                'rerendering_saved'       => $rerendering_saved,
                'breadcrumbs'             => $this->getBreadcrumbGenerator()->buildNewTicket(),
                'page_title'              => $this->createPageTitle()->newticket(),
                'form_errors'             => $form->isSubmitted() ? $form->getErrors() : [],
                'show_ticket_suggestions' => $show_ticket_suggestions,
                'lockout'                 => $abuseCheck->isLockoutRecommended(),
                'lockout_time'            => $abuseCheck->getLockoutTime(true),
            ]
        );
    }

    /**
     * @Route("/thank-you/verify-email", name="portal_thanks_verify", defaults={"ticket_ref" = null, "do_verify" = true})
     * @Route("/thank-you/{ticket_ref}", name="portal_thanks", defaults={"ticket_ref" = null})
     *
     * @param Request $request
     * @param null    $ticket_ref
     * @param bool    $do_verify
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function thankYouAction(Request $request, $ticket_ref = null, $do_verify = false)
    {
        $create_pw_link    = $request->get('create_pw_link');
        $is_confirmed_user = $request->get('is_confirmed_user');

        return $this->renderThemeView('Theme:Tickets:thank_you.html.twig', [
            'ticket_ref'        => $ticket_ref,
            'page_title'        => $this->createPageTitle()->newticketGuestThankYou(),
            'verify_email'      => $do_verify,
            'create_pw_link'    => $create_pw_link,
            'is_confirmed_user' => $is_confirmed_user,
        ]);
    }

    /**
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function onSavedTicket(Ticket $ticket, Request $request)
    {
        $person = $ticket->getPerson();
        $this->addFlash('success', $this->phrase('portal.flashes.ticket_created'));

        // IF this person can't login but they are confirmed. show the thank you screen, but on that screen give
        // them a link to setup an account straight away if they want to
        $destination    = $this->getObjectRouter()->getPortalPath($ticket);
        $create_pw_link = null;

        $redirect = $this->get('portal_validation')->getPasswordRedirectIfRequired($person, $request, $destination);
        if ($redirect) {
            $create_pw_link = $redirect->getTargetUrl();
        }

        $params = [
            'ticket_ref'        => $person->isUser() ? $this->get('ticket.public_id_resolver')->findId($ticket) : null,
            'create_pw_link'    => $create_pw_link,
            'is_confirmed_user' => $person->isConfirmed(),
        ];

        return $this->redirectToRoute('portal_thanks', $params);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Ticket\NewTicket
     */
    protected function getNewTicketService()
    {
        return $this->get('tickets.new_ticket');
    }
}
