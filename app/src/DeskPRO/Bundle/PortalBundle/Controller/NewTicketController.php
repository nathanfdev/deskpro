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
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Person\EmailValidationRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewTicketController.
 */
class NewTicketController extends AbstractController
{
    /**
     * @Route("/new-ticket", name="portal_new_ticket")
     * @Route("/new-ticket", name="user_tickets_new")
     * @Security("is_granted('USE_TICKETS')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param string  $visitor_id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newTicketAction(Request $request, $visitor_id)
    {
        $new_ticket_service = $this->get('tickets.new_ticket');

        $person         = $this->getUser() ?: new PersonGuest();
        $ticket         = $this->getTicketManager()->createTicket();
        $ticket_message = new TicketMessage();
        $ticket_message->setVisitorId($visitor_id);
        $ticket_message->setIpAddress($request->getClientIp());
        $ticket->setPerson($person);
        $ticket_message->setPerson($person);
        $ticket->addMessage($ticket_message);
        $lang = $this->get('language_manager')->getLanguageStack()->getActiveOrDefault();
        $ticket->setLanguage($lang);

        // do a one through with the GET request to update our model before starting the "real" form
        $form = $this->createForm('ticket', $ticket, [
            'person'            => $person,
            'ticket_message'    => $ticket_message,
            'method'            => 'GET',
            'validation_groups' => false,
            'settings'          => $this->getBrandContainer()->getSettings(),
            'action'            => $this->generateUrl('portal_new_ticket'),
        ]);
        $form->submit($request->query->get('ticket', []), false);

        foreach ($ticket_message->getAttachments() as $attachment) {
            if (!$attachment->getBlob()) {
                $ticket_message->removeAttachment($attachment);
            }
        }

        $form = $this->createForm('ticket', $ticket, [
            'person'                => $person,
            'ticket_message'        => $ticket_message,
            'settings'              => $this->getBrandContainer()->getSettings(),
            'action'                => $this->generateUrl('portal_new_ticket'),
            'attr'                  => ['data-save-draft' => 'new_ticket'],
            'saved_form_subrequest' => $this->isSavedFormSubRequest($request),
            'allow_extra_fields'    => true,
        ]);
        $form->handleRequest($request);

        $rerendering       = $form->has('rerender_form');
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
                            $this->getPersonFactory()->checkGuestForValidation($person, $this->isSavedFormSubRequest($request));

                            // the below block only executes during a saved form request (they clicked validation link)
                            $email  = $person->getPrimaryEmail();
                            $person = $this->getPersonDataService()->getPersonForEmail($email->getEmail());

                            // since the guest is set on the form, we need to update all of the associations
                            $ticket->setPerson($person);
                            $ticket_message->setPerson($person);
                            foreach ($ticket_message->getAttachments() as $attachment) {
                                $blob = $attachment->getBlob();
                                if ($blob) {
                                    $blob->is_temp = false;
                                }

                                $attachment->setPerson($person);
                            }

                            $new_ticket = $new_ticket_service->acceptNewTicket($ticket, $person, $request);

                            return $this->onSavedTicket($new_ticket, $person, $request);
                        } catch (LoginRequiredException $e) {
                            // the email used belongs to a user, and brand settings say they need to log in
                            $person = $e->getPerson();

                            return $this->getFormSaver()->saveFormForPersonLogin(SavedForm::TYPE_NEW_TICKET, $person, $form, $request);
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
                                $new_ticket = $new_ticket_service->acceptNewTicketForGuest($ticket, $ticket_message, $person, $request);

                                return $this->onSavedTicket($new_ticket, $person, $request);
                            }
                        }
                    }

                    $new_ticket = $new_ticket_service->acceptNewTicket($ticket, $person, $request);

                    return $this->onSavedTicket($new_ticket, $person, $request);
                }
            }
        } elseif ($form->isSubmitted()) {
            $new_ticket_service->submitNewTicketAbuseCheck($person, $request->getClientIp());
        }

        $form_full = $this->createForm('ticket', $ticket, [
            'person'         => $person,
            'ticket_message' => null,
            'settings'       => $this->getBrandContainer()->getSettings(),
            'full_version'   => true,
            'action'         => $this->generateUrl('portal_new_ticket'),
        ]);

        /** @var \Application\DeskPRO\TicketLayout\LayoutCollection $layouts */
        $layouts           = $this->getContainer()->getTicketLayoutManager()->getUserLayouts(true);
        $ticket_display_js = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewTicket();

        // show ticket deflection? (suggestions)
        $show_ticket_suggestions = (bool) $this->getBrandSetting('core.show_ticket_suggestions');

        return $this->renderThemeView(
            'Theme:NewTicket:new_ticket.html.twig', [
                'form'                    => $form->createView(),
                'form_full'               => $form_full->createView(),
                'ticket_display_js'       => $ticket_display_js,
                'rerendering'             => $rerendering,
                'rerendering_saved'       => $rerendering_saved,
                'breadcrumbs'             => $breadcrumbs,
                'page_title'              => $this->createPageTitle()->newticket(),
                'form_errors'             => $form->isSubmitted() ? $form->getErrors() : [],
                'show_ticket_suggestions' => $show_ticket_suggestions,
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
     * @param Person  $person
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function onSavedTicket(Ticket $ticket, Person $person, Request $request)
    {
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
}
