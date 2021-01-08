<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFeedback;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetUseLogRepository;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebFullType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsWebType;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimelinePagerfantaAdapter;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\CsrfType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\TicketAddCcType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\TicketFeedbackType;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\TicketReplyType;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use DeskPRO\Bundle\PortalBundle\Routing\RedirectToUrlException;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTablesCollection;
use DeskPRO\Component\Pdf\PdfRendererInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TicketsController extends AbstractController
{
    /**
     * @Route("/tickets/resolved/{type}", name="portal_tickets_resolved", defaults={"type":"own", "resolved_only": true}, requirements={"type":"organization"})
     * @Route("/tickets/{type}", name="portal_tickets", defaults={"type":"own"}, requirements={"type":"organization"})
     * @Route("/tickets", name="user_tickets")
     * @Route("/tickets/organization", name="user_tickets_organization", defaults={"type":"organization"})
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param $type
     * @param bool $resolved_only
     *
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request, $type, $resolved_only = false)
    {
        $person = $this->getUser();

        // access to organization list?
        if ($type === 'organization' && !($person->organization && $person->organization_manager)) {
            return $this->redirectToRoute('portal_tickets');
        }

        // create ticket list tables
        if ($this->isHelpCenterTheme()) {
            $ticketCategories =
                [
                    TicketFilter::CATEGORY_AWAITING_USER  => $this->phrase('helpcenter.tickets.list_status_user'),
                    TicketFilter::CATEGORY_AWAITING_AGENT => $this->phrase('helpcenter.tickets.list_status_agent'),
                    TicketFilter::CATEGORY_RESOLVED       => $this->phrase('helpcenter.tickets.list_status_resolved'),
                ];
        } else {
            $ticketCategories = $resolved_only ?
                [
                    TicketFilter::CATEGORY_RESOLVED => $this->phrase(['portal.tickets.list_status_resolved', 'helpcenter.tickets.list_status_resolved']),
                ]
                :
                [
                    TicketFilter::CATEGORY_AWAITING_USER  => $this->phrase(['portal.tickets.list_status_user', 'helpcenter.tickets.list_status_user']),
                    TicketFilter::CATEGORY_AWAITING_AGENT => $this->phrase(['portal.tickets.list_status_agent', 'helpcenter.tickets.list_status_agent']),
                ];
        }
        /* @var TicketListTable[] $tables */
        $tables = $this->makeTicketListTables($type, $ticketCategories, $person, $request);

        $ticketListJs = 'window.DESKPRO_TICKET_LIST_TABLES = '.$tables->compileJsObj().';';

        return $this->renderThemeView(
            'Theme:Tickets:index.html.twig',
            [
                'ticket_list_tables'      => $tables,
                'resolved_only'           => $resolved_only,
                'awaiting_response_count' => $this->getAwaitingUserCount($type, $person),
                'type'                    => $type,
                'person'                  => $person,
                'breadcrumbs'             => $this->getBreadcrumbGenerator()->buildTicketList(),
                'page_title'              => $this->createPageTitle()->tickets(),
                'ticket_list_js'          => $ticketListJs,
                'search_query'            => $request->query->get('q', ''),
            ]
        );
    }

    /**
     * @param $type
     * @param array   $categories
     * @param Person  $person
     * @param Request $request
     *
     * @return TicketListTablesCollection
     */
    protected function makeTicketListTables($type, array $categories, Person $person, Request $request)
    {
        $tables = new TicketListTablesCollection();

        foreach ($categories as $category => $title) {
            $tables->addTable(
                $this->get('tickets.table')->makeTicketTable($person, $request, $type, $category, $title)
            );
        }

        return $tables;
    }

    /**
     * @Route("/tickets/{ticket_ref}", name="portal_tickets_view")
     * @Route("/ticket-view/{auth}", name="portal_tickets_guest_view")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param string  $ticket_ref
     * @param string  $auth
     * @param string  $visitor_id
     * @param string  $_route
     *
     * @return Response
     */
    public function viewAction(Request $request, $ticket_ref = null, $auth = null, $visitor_id = null, $_route = null)
    {
        $ticket = $this->getTicketForViewPage($ticket_ref, $auth, $_route);

        if ($_route === 'portal_tickets_view') {
            $this->denyAccessUnlessGranted(TicketsVoter::TICKET_VIEW, $ticket);
        }

        if ($_route === 'portal_tickets_guest_view') {
            $this->denyAccessUnlessGranted(TicketsVoter::TICKET_VIEW_AUTH, $ticket);
        }

        $form_data = [
            'ticket_message' => $message = new TicketMessage(),
            'attachments'    => new ArrayCollection(),
        ];

        $message->setVisitorId($visitor_id);
        $message->setIpAddress($request->getClientIp());

        $form = $this->createForm(TicketReplyType::class, $form_data, [
            'ticket'         => $ticket,
            'ticket_message' => $message,
            'person'         => $this->getUser(),
            'settings'       => $this->getBrandContainer()->getSettings(),
        ]);

        $form->setData(['ticket' => $ticket]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // we don't process the reply if they simply clicked the "add more attachments" button (non-JS users)
            if (!$form->getClickedButton() || $form->getClickedButton()->getConfig()->getName() !== 'more_attachments') {
                if ($ticket->isResolved() && !$this->isGranted(TicketsVoter::TICKET_REOPEN_RESOLVED, $ticket)) {
                    $this->addFlash('error', $this->phrase(['user.error.permission-denied', 'helpcenter.error.permission_denied']));

                    return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
                }

                $this->addCurrentUserAsParticipantIfTheyAreNot($ticket);

                $this->saveNewReply($ticket, $message);

                $this->addFlash('success', $this->phrase(['portal.flashes.ticket_replied', 'helpcenter.flashes.ticket_replied']));

                $ccsRemovedValue = $form['cc_remove']->getData();

                if ($ccsRemovedValue) {
                    $ccsRemoved = explode(',', $ccsRemovedValue);
                    foreach ($ccsRemoved as $ccRemoved) {
                        if (!$ccRemoved) {
                            continue;
                        }
                        $participant = $this->getEm()->getRepository(TicketParticipant::class)->find($ccRemoved);
                        if (!$participant instanceof TicketParticipant) {
                            continue;
                        }
                        if ($participant->getTicket() === $ticket) {
                            $ccPerson = $participant->getPerson();
                            $ticket->removeParticipantPerson($ccPerson);
                            $this->getEm()->flush();
                            $this->addFlash('success', $this->phrase(['portal.flashes.ticket_participant_remove', 'helpcenter.flashes.ticket_participant_remove'], [
                                'name'  => $ccPerson->getDisplayNameUser(),
                                'email' => $ccPerson->getPrimaryEmailAddress(),
                            ]));
                        }
                    }
                }

                return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
            }
        }

        $ticketView = $this->getTicketsViewService()->getUserTicketView($ticket, $this->getUser());

        // create timeline with pagination
        $page    = $request->get('page', 'last');
        $perPage = 10;
        if ($this->isHelpCenterTheme()) {
            $timeline = $this->get('data.ticket_timeline')->getHcUserTimeline($ticket, $page, $perPage, $this->getUser());
        } else {
            $timeline = $this->get('data.ticket_timeline')->getUserTimeline($ticket, $page, $perPage, $this->getUser());
        }
        $pager    = new Pagerfanta(new TicketTimelinePagerfantaAdapter($timeline));
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage($page === 'last' ? $pager->getNbPages() : $page);

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketView($ticket);

        [$lastUserReplyInSeconds, $createdInSeconds] = $this->getRecentTimes($ticket);

        $canReply = $ticket->isOwner($this->getUser())
            || (!$this->getUser()->isAgent() && $ticket->isParticipant($this->getUser()))
            || $ticket->isOrganizationManager($this->getUser());

        $csrfForm  = $this->createForm(CsrfType::class);
        $addCcForm = $this->createForm(TicketAddCcType::class);

        return $this->renderThemeView('Theme:Tickets:view.html.twig', [
            'ticket'                     => $ticket,
            'ticket_view'                => $ticketView,
            'timeline_pager'             => $pager,
            'timeline'                   => $timeline,
            'can_edit'                   => $this->isGranted(TicketsVoter::TICKET_EDIT, $ticket),
            'can_reply'                  => $canReply,
            'form'                       => $form->createView(),
            'breadcrumbs'                => $breadcrumbs,
            'page_title'                 => $this->createPageTitle()->tickets($ticket),
            'last_user_reply_in_seconds' => $lastUserReplyInSeconds,
            'created_in_seconds'         => $createdInSeconds,
            'edit_page'                  => false,
            'form_errors'                => $form->isSubmitted() ? $form->getErrors() : [],
            'csrf_form'                  => $csrfForm->createView(),
            'add_cc_form'                => $addCcForm->createView(),
        ]);
    }

    /**
     * @Route("/tickets/{ticket_ref}/edit", name="portal_tickets_edit")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param string  $ticket_ref
     *
     * @return Response
     */
    public function editAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw $this->createNotFoundException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        $this->denyAccessUnlessGranted(TicketsVoter::TICKET_EDIT, $ticket);

        $person = $this->getUser();
        $form   = $this->createForm(TicketWithLayoutsWebType::class, $ticket, [
            'person'              => $person,
            'department_id'       => $request->query->getInt('department_id'),
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_EDIT,
        ]);

        $form->handleRequest($request);

        $rerendering = $form->has('rerender_form');

        if ($form->isValid()) {
            // if the form set a hidden field "rerender_form" then we want to skip actual processing for now
            if (!$rerendering) {
                $this->saveEditedTicket($ticket, $person);

                $this->addFlash('success', $this->phrase(['portal.flashes.ticket_updated', 'helpcenter.flashes.ticket_updated']));

                return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
            }
        }

        // BREADCRUMBS
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketEdit($ticket);
        $ticket_view = $this->getTicketsViewService()->getUserTicketView($ticket, $person);

        [$last_user_reply_in_seconds, $created_in_seconds] = $this->getRecentTimes($ticket);

        // Need to pass Ticket and Person to properly show/get person custom fields and values
        // Might use them in case of dependend fields in criteria
        $fullFormOptions = [
            'action'              => $this->generateUrl('portal_tickets_edit', ['ticket_ref' => $ticket->getPublicId()]),
            'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_EDIT,
        ];
        if ($person && !$person instanceof PersonGuest) {
            $fullFormOptions['person'] = $person;
        }
        $form_full         = $this->createForm(TicketWithLayoutsWebFullType::class, null, $fullFormOptions);
        $layouts           = $this->getContainer()->getTicketLayoutManager()->getUserLayouts(true);
        $ticket_display_js = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        $csrfForm  = $this->createForm(CsrfType::class);
        $addCcForm = $this->createForm(TicketAddCcType::class);

        return $this->renderThemeView('Theme:Tickets:edit.html.twig', [
            'ticket'                     => $ticket,
            'form'                       => $form->createView(),
            'rerendering'                => $rerendering,
            'breadcrumbs'                => $breadcrumbs,
            'page_title'                 => $this->createPageTitle()->tickets($ticket),
            'last_user_reply_in_seconds' => $last_user_reply_in_seconds,
            'created_in_seconds'         => $created_in_seconds,
            'edit_page'                  => true,
            'can_edit'                   => $this->isGranted(TicketsVoter::TICKET_EDIT, $ticket),
            'ticket_view'                => $ticket_view,
            'ticket_display_js'          => $ticket_display_js,
            'form_full'                  => $form_full->createView(),
            'csrf_form'                  => $csrfForm->createView(),
            'add_cc_form'                => $addCcForm->createView(),
        ]);
    }

    /**
     * @Route("/tickets/{ticket_ref}/resolve", name="portal_tickets_resolve")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param string  $ticket_ref
     *
     * @return Response
     */
    public function resolveTicketAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        // already resolved, no need to proceed
        if ($ticket->isResolved()) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        $person = $this->getUser();

        $csrfForm = $this->createForm(CsrfType::class);
        $csrfForm->handleRequest($request);

        if ($csrfForm->isValid() || ($request->getMethod() === 'POST' && !count($csrfForm->all()))) {
            $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->findStatusOrException(TicketStatus::STATUS_TYPE_RESOLVED));
            $this->saveEditedTicket($ticket, $person);
            $this->addFlash('success', $this->phrase(['portal.flashes.ticket_resolved', 'helpcenter.flashes.ticket_resolved']));

            if ($this->get('settings_resolver')->getGlobalSettings()->get('core_tickets.enable_feedback')) {
                if ($request->isXmlHttpRequest()) {
                    return $this->rateTicketAction($request, $ticket->getRef(), $ticket->getAuth());
                } else {
                    return $this->redirectToRoute(
                        'portal_tickets_feedback',
                        [
                            'auth'       => $ticket->getAuth(),
                            'ticket_ref' => $ticket->getRef(),
                        ]
                    );
                }
            }

            return $this->redirectToRoute('portal_tickets_view', [
                'ticket_ref' => $ticket->getRef(),
            ]);
        }

        if ($csrfForm->isSubmitted() && $request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        return $this->renderThemeView('Theme:Tickets:resolve.html.twig', [
            'ticket'      => $ticket,
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildTicketEdit($ticket),
            'page_title'  => $this->createPageTitle()->tickets($ticket),
            'form'        => $csrfForm->createView(),
        ]);
    }

    /**
     * @Route("/tickets/{ticket_ref}/add-cc", name="portal_tickets_cc_add")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param string  $ticket_ref
     *
     * @return Response|NotFoundHttpException
     */
    public function addCcAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $redirectResponse = $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $ticket_ref]);

        $form = $this->createForm(TicketAddCcType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $name  = $form->get('name')->getData();
            $email = $form->get('email')->getData();
            $maxCc = (int) $this->getBrandSetting('core_tickets.email_cc_max_count');
            if ($maxCc && $ticket->getCcs()->count() >= $maxCc) {
                if ($request->isXmlHttpRequest()) {
                    return $this->makeJsonResponse([
                        'error' => $this->phrase('helpcenter.flashes.ticket_participant_cc_limit_reached', ['max' => $maxCc]),
                    ]);
                } else {
                    $this->addFlash('error', $this->phrase(['portal.flashes.ticket_participant_cc_limit_reached', 'helpcenter.flashes.ticket_participant_cc_limit_reached'], ['max' => $maxCc]));

                    return $redirectResponse;
                }
            }

            $personFactory = $this->get('person_factory');
            $context       = new CreatePersonContext('gateway.person');
            $person        = $personFactory->getOrCreatePersonByEmail($email, $context);

            if ($person) {
                // only set the name if this email doesn't have a name (a new person)
                // otherwise anyone can CC a person and change their name in the system...
                if ($name && !$person->getFirstName()) {
                    $person->name = $name;
                }

                if ($ticket->hasParticipantPerson($person)) {
                    if ($request->isXmlHttpRequest()) {
                        return $this->makeJsonResponse([
                            'error' => $this->phrase('helpcenter.flashes.ticket_participant_already_error'),
                        ]);
                    } else {
                        $this->addFlash('success', $this->phrase(['portal.flashes.ticket_participant_already_error', 'helpcenter.flashes.ticket_participant_already_error']));

                        return $redirectResponse;
                    }
                }

                $participant = new TicketParticipant();
                $participant->setPerson($person);
                $ticket->addParticipant($participant);

                $this->getEm()->persist($participant);
                $this->getEm()->flush();

                if ($request->isXmlHttpRequest()) {
                    return $this->makeJsonResponse([
                        'success' => true,
                        'html'    => $this->renderThemeView('Theme:Tickets:embeds/ticket_cc_sidebar.html.twig', [
                            'person'      => $participant->getPerson(),
                            'avatar_link' => $this->generateUrl('portal_tickets_cc_remove', ['ticket_ref' => $ticket->getPublicId(), 'cc_id' => $participant->getId()]),
                            'cc_id'       => $participant->getId(),
                        ])->getContent(),
                    ]);
                } else {
                    // return success
                    $this->addFlash('success', $this->phrase(['portal.flashes.ticket_participant_add', 'helpcenter.flashes.ticket_participant_add'], [
                        'name'  => $person->getDisplayNameUser(),
                        'email' => $person->getPrimaryEmailAddress(),
                    ]));

                    return $redirectResponse;
                }
            }
        }

        if (count($form->get('email')->getErrors()) > 0) {
            if ($request->isXmlHttpRequest()) {
                return $this->makeJsonResponse(
                    [
                        'errors' => [
                            'email' => $this->phrase('helpcenter.flashes.ticket_participant_email_error'),
                        ],
                    ]
                );
            } else {
                // return error with email
                $this->addFlash('error', $this->phrase(['portal.flashes.ticket_participant_email_error', 'helpcenter.flashes.ticket_participant_email_error']));
            }
        } else {
            // return general error
            return $this->createNotFoundException();
        }

        return $redirectResponse;
    }

    /**
     * @Route("/tickets/{ticket_ref}/remove-cc/{cc_id}", name="portal_tickets_cc_remove")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     * @AutoPostOnGetRequest()
     *
     * @param mixed $ticket_ref
     * @param mixed $cc_id
     */
    public function removeCcAction(Request $request, $ticket_ref, $cc_id)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $redirectResponse = $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $ticket_ref]);

        $participant = $this->getEm()->getRepository(TicketParticipant::class)->find($cc_id);
        if (!$participant instanceof TicketParticipant) {
            return $redirectResponse;
        }

        // the passed participant must be a participant on the passed ticket ref
        if ($participant->getTicket() === $ticket) {
            $ccPerson = $participant->getPerson();

            $ticket->removeParticipantPerson($ccPerson);
            $this->getEm()->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->makeJsonResponse([
                    'success' => true,
                ]);
            } else {
                $this->addFlash('success', $this->phrase(['portal.flashes.ticket_participant_remove', 'helpcenter.flashes.ticket_participant_remove'], [
                    'name'  => $ccPerson->getDisplayNameUser(),
                    'email' => $ccPerson->getPrimaryEmailAddress(),
                ]));

                return $redirectResponse;
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->makeJsonResponse([
                'error' => true,
            ]);
        } else {
            // return general error, likely the participant id and ticket id are not the same, which would be bad!
            $this->addFlash(
                'error',
                $this->phrase(
                    [
                        'portal.flashes.ticket_participant_remove_unknown_error',
                        'helpcenter.flashes.ticket_participant_remove_unknown_error',
                    ]
                )
            );
        }

        return $redirectResponse;
    }

    /**
     * @Route("/ticket-rate/{ticket_ref}/{auth}/{message_id}", name="portal_tickets_feedback", defaults={"message_id"=null})
     * @Route("/ticket-rate/{ticket_ref}/{auth}/{message_id}", name="user_tickets_feedback", defaults={"message_id"=null})
     *
     * @param Request $request
     * @param $ticket_ref
     * @param $auth
     * @param null $message_id
     *
     * @return RedirectResponse|Response
     * @throws OptimisticLockException
     * @throws NonUniqueResultException|NoResultException
     */
    public function rateTicketAction(Request $request, $ticket_ref, $auth, $message_id = null)
    {
        if (!$ticket = $this->getTicketByRef($ticket_ref)) {
            throw new NotFoundHttpException('ref not found');
        }

        if ((string) $ticket->getAuth() !== (string) $auth) {
            throw new NotFoundHttpException('auth does not match ticket');
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $ticketMessageRepo */
        $ticketMessageRepo = $this->getRepo(TicketMessage::class);
        if ($message_id) {
            /** @var \Application\DeskPRO\Entity\TicketMessage $message */
            $message = $ticketMessageRepo->find($message_id);
        } else {
            /** @var \Application\DeskPRO\Entity\TicketMessage $message */
            $message = $ticketMessageRepo->getLastAgentReply($ticket);
        }

        if (!$message) {
            $message = $ticketMessageRepo->getFirstTicketMessage($ticket);
        }

        // message must exist and belong to the ticket requested
        if (!$message || $message->getTicketId() !== $ticket->getId()) {
            if (!$this->getBrandSetting('core.iface_portal')) {
                throw new NotFoundHttpException();
            }

            return $this->redirectToRoute('portal_tickets_view', ['ticket_ref' => $ticket_ref]);
        }

        $person = $this->getAuthenticatedUserOrTicketPerson($ticket);

        /** @var \Application\DeskPRO\EntityRepository\TicketFeedback $ticketFeedbackRepo */
        $ticketFeedbackRepo = $this->getRepo(TicketFeedback::class);
        $feedback           = $ticketFeedbackRepo->getFeedback($message, $person, true);

        $rating = $request->get('setrating');
        if (null !== $rating && is_numeric($rating)) {
            $feedback->setRating($rating);
        }

        if (null !== $rating && null === $feedback->getId() && null !== $feedback->getRating()) {
            $this->getEm()->persist($feedback);
            $this->getEm()->flush();
            $this->addFlash('success', $this->phrase(['portal.flashes.ticket_feedback_thank_you', 'helpcenter.flashes.ticket_feedback_thank_you']));
        }

        $form = $this->createForm(TicketFeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->updateTicketFeedbackRating($ticket, $message, $feedback);

            /** @var SnippetUseLogRepository $snippetUseLogRepo */
            $snippetUseLogRepo = $this->getRepo(SnippetUseLog::class);
            $uses              = $snippetUseLogRepo->getLogsByTicketMessage($message);

            /** @var SnippetUseLog $use */
            foreach ($uses as $use) {
                $snippet = $use->getSnippet();
                // Compensate previous answered feedback
                if ($use->getRating() !== null) {
                    switch ($use->getRating()) {
                        case TicketFeedback::RATE_POSITIVE:
                            $snippet->setPositiveRatings((int) $snippet->getPositiveRatings() - 1);

                            break;
                        case TicketFeedback::RATE_NEUTRAL:
                            $snippet->setNeutralRatings((int) $snippet->getNeutralRatings() - 1);

                            break;
                        case TicketFeedback::RATE_NEGATIVE:
                            $snippet->setNegativeRatings((int) $snippet->getNegativeRatings() - 1);

                            break;
                        default:
                            break;
                    }
                }
                $use->setRating($feedback->getRating());
                switch ($feedback->getRating()) {
                    case TicketFeedback::RATE_POSITIVE:
                        $snippet->setPositiveRatings((int) $snippet->getPositiveRatings() + 1);

                        break;
                    case TicketFeedback::RATE_NEUTRAL:
                        $snippet->setNeutralRatings((int) $snippet->getNeutralRatings() + 1);

                        break;
                    case TicketFeedback::RATE_NEGATIVE:
                        $snippet->setNegativeRatings((int) $snippet->getNegativeRatings() + 1);

                        break;
                    default:
                        break;
                }
                $this->getEm()->persist($snippet);
                $this->getEm()->persist($use);
            }

            $this->getEm()->persist($feedback);
            $this->getEm()->flush();

            $this->addFlash('success', $this->phrase(['portal.flashes.ticket_feedback_thank_you', 'helpcenter.flashes.ticket_feedback_thank_you']));

            if ($this->getBrandSetting('core.iface_portal')) {
                return $this->redirectToRoute('portal_home');
            }
        } elseif ($form->isSubmitted()) {
            // return general error
            $this->addFlash('error', $this->phrase(['portal.flashes.ticket_feedback_unknown_error', 'helpcenter.flashes.ticket_feedback_unknown_error']));
        }

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketView($ticket);

        $template = 'Theme:Tickets:feedback.html.twig';

        if (!$this->getBrandSetting('core.iface_portal')) {
            $template = 'Theme:Tickets:feedback-simple.html.twig';
        }

        if ($request->isXmlHttpRequest()) {
            $template = 'Theme:Tickets:ajax-feedback.html.twig';
        }

        return $this->renderThemeView($template, [
            'page_title'  => $this->get('portal_view.page_title_generator')->tickets($ticket),
            'breadcrumbs' => $breadcrumbs,
            'ticket'      => $ticket,
            'message'     => $message,
            'feedback'    => $feedback,
            'setrating'   => $request->get('setrating') !== null,
            'form'        => $form->createView(),
        ]);
    }

    /**
     * @Route("/tickets/{ticket_ref}/unresolve", name="portal_tickets_unresolve")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     * @AutoPostOnGetRequest()
     *
     * @param mixed $ticket_ref
     */
    public function unresolveTicketAction(Request $request, $ticket_ref)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw new NotFoundHttpException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW, $ticket)) {
            throw new AccessDeniedException();
        }

        $person = $this->getUser();

        if (!$ticket->isResolved()) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        if (!$this->isGranted(TicketsVoter::TICKET_REOPEN_RESOLVED, $ticket)) {
            return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
        }

        $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->findStatusOrException(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
        $this->saveEditedTicket($ticket, $person);
        $this->addFlash('success', $this->phrase(['portal.flashes.ticket_re_opened', 'helpcenter.flashes.ticket_re_opened']));

        return $this->redirect($this->getObjectRouter()->getPortalPath($ticket));
    }

    /**
     * @Route("/tickets/pdf/{ticketRef}", name="portal_tickets_pdf")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_TICKETS')")
     *
     * @param Request $request
     * @param string $ticketRef
     * @return Response
     */
    public function pdfAction(Request $request, $ticketRef = null)
    {
        if (!$ticket = $this->getTicketByRefOrId($ticketRef)) {
            throw $this->createNotFoundException(sprintf('no ticket with ref or id "%s" found', $ticketRef));
        }

        $this->denyAccessUnlessGranted(TicketsVoter::TICKET_VIEW, $ticket);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildTicketView($ticket);

        $ticketView = $this->getTicketsViewService()->getUserTicketView($ticket, $this->getUser());

        $ticketMessagesBlock = $this->renderView(
            'DeskPRO:pdf_agent:ticket-messages-batch.html.twig',
            [
                'ticket'                     => $ticket,
                'ticket_messages'            => $ticket->getDisplayableMessages(),
            ]
        );

        $layout = $this->container->getTicketLayoutManager()->getUserLayouts()->getLayout(
            $ticket->getDepartmentId()
        );

        $viewLayout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);

        $fieldManager = $this->container->getTicketFieldManager();
        $customFields = $fieldManager->getDisplayArrayForObject($ticket);

        $contentHtml = $this->renderThemeView('Theme:Tickets:pdf.html.twig', [
            'ticket'                => $ticket,
            'ticket_view'           => $ticketView,
            'breadcrumbs'           => $breadcrumbs,
            'ticket_messages_block' => $ticketMessagesBlock,
            'layout'                => $viewLayout,
            'custom_fields'         => $customFields,
        ], false);

        /** @var PdfRendererInterface $pdfRenderer */
        $pdfRenderer = $this->get('pdf_renderer');

        $pdf = $pdfRenderer->render($contentHtml);

        $response = new Response();

        $response->setContent($pdf);
        $response->headers->set('Content-Disposition', 'attachment; filename=Ticket_'.$ticket->getRef().'.pdf');
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * ticket_ref can either be an ID or a ref depending on settings.
     *
     * @param $ticket_ref
     *
     * @return Ticket|null
     */
    protected function getTicketByRefOrId($ticket_ref)
    {
        if ($this->getBrandSetting('core_tickets.use_ref')) {
            if (!$ticket = $this->getTicketByRef($ticket_ref)) {
                if ($ticket = $this->getTicketById($ticket_ref)) {
                    if ($ticket->getPersonId() === $this->getCurrentPerson()->getId()) {
                        // we allow the "other" id to be used only if its the currently logged in user's own ticket
                        throw new RedirectToUrlException($this->generateUrl('portal_tickets_view', ['ticket_ref' => $ticket->getRef()]));
                    } else {
                        throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
                    }
                }
            }
        } else {
            if (!$ticket = $this->getTicketById($ticket_ref)) {
                if ($ticket = $this->getTicketByRef($ticket_ref)) {
                    if ($ticket->getPersonId() === $this->getCurrentPerson()->getId()) {
                        // we allow the "other" id to be used only if its the currently logged in user's own ticket
                        throw new RedirectToUrlException($this->generateUrl('portal_tickets_view', ['ticket_ref' => $ticket->getId()]));
                    } else {
                        throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
                    }
                }
            }
        }

        if (!$ticket) {
            throw new NotFoundHttpException(sprintf('ticket not found for "%s"', $ticket_ref));
        }

        if ($ticket->hasNotesOnly()) {
            throw new NotFoundHttpException(sprintf('ticket with ref or id "%s" found but has only agent notes', $ticket_ref));
        }

        return $ticket;
    }

    /**
     * @param $ticket_ref
     *
     * @return Ticket
     */
    protected function getTicketByRef($ticket_ref)
    {
        return $this->getRepo(Ticket::class)->findOneBy(['ref' => $ticket_ref]);
    }

    /**
     * @param $auth
     *
     * @return Ticket
     */
    protected function getTicketByAuthIfGrantedAccess($auth)
    {
        $ticket = $this->getRepo(Ticket::class)->findOneBy(['auth' => $auth]);

        if (!$this->isGranted(TicketsVoter::TICKET_VIEW_AUTH, $ticket)) {
            // the user has a valid auth code for a ticket, but isn't logged in
            // throwing this will initiate the normal login routine, but you
            // can set a flash message here, or do a redirect to /login with some GET param to show a specific
            // error message instead
            // this will simply show the login screen with no message
            throw $this->createAccessDeniedException(
                sprintf('user does not have access to view this ticket with the auth code "%s"', $auth)
            );
        }

        return $ticket;
    }

    /**
     * @param $ticket_ref
     *
     * @return Ticket
     */
    protected function getTicketById($ticket_ref)
    {
        return $this->getRepo(Ticket::class)->findOneBy(['id' => $ticket_ref]);
    }

    private function saveEditedTicket(Ticket $ticket, Person $person, $event_type = TicketTrigger::EVENT_TYPE_UPDATE)
    {
        $ticket_manager = $this->getTicketManager();
        $ticket_manager->markAsManaged($ticket);

        $em = $this->getEm();
        $em->beginTransaction();

        try {
            $em->persist($ticket);

            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $context = $ticket_manager->createUserExecutorContext($person, $event_type, 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw $e;
        }

        return $ticket;
    }

    private function saveNewReply(Ticket $ticket, TicketMessage $message, $event_type = TicketTrigger::EVENT_TYPE_NEWREPLY)
    {
        $person = $message->person;

        $ticket_manager = $this->getTicketManager();
        $ticket_manager->markAsManaged($ticket);

        $em = $this->getEm();
        $em->beginTransaction();

        try {
            foreach ($message->getAttachments() as $attachment) {
                if ($blob = $attachment->getBlob()) {
                    $blob->is_temp = false;
                }
                $attachment->setPerson($person);
            }

            $ticket->addMessage($message);

            // If status is pending, we'll switch it to open so agents will see it
            if (in_array(
                $ticket->getStatus(),
                [
                    TicketStatus::STATUS_TYPE_AWAITING_USER,
                    TicketStatus::STATUS_TYPE_RESOLVED,
                    TicketStatus::STATUS_TYPE_PENDING,
                ]
            )) {
                $ticket->setTicketStatus($this->getContainer()->getTicketStatuses()->findStatusOrException(TicketStatus::STATUS_TYPE_AWAITING_AGENT));
            }

            if ($person->getId() && !$ticket->hasParticipantPerson($person)) {
                // someone like the org manager replying - need to make sure they're CC'd
                $ticket->addParticipantPerson($person);
            }

            $em->persist($ticket);
            $em->persist($message);

            $context = $ticket_manager->createUserExecutorContext($person, $event_type, 'portal');

            $ticket_manager->saveTicket($ticket, $context);
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();

            throw $e;
        }

        return $ticket;
    }

    /**
     * @param $ticket
     *
     * @return array
     */
    protected function getRecentTimes($ticket)
    {
        $last_user_reply_in_seconds = null;
        if ($last_reply = $ticket->date_last_user_reply) {
            $last_reply                 = Carbon::createFromTimestamp($last_reply->getTimestamp());
            $last_user_reply_in_seconds = $last_reply->diffInSeconds();
        }

        $created            = Carbon::createFromTimestamp($ticket->date_created->getTimestamp());
        $created_in_seconds = $created->diffInSeconds();

        return [$last_user_reply_in_seconds, $created_in_seconds];
    }

    /**
     * If the user is authenticated use that person, else use the ticket person.
     *
     * @param Ticket $ticket
     *
     * @return Person
     */
    private function getAuthenticatedUserOrTicketPerson(Ticket $ticket)
    {
        $current_person = $this->getCurrentPerson();
        if (!$current_person instanceof PersonGuest) {
            return $current_person;
        }

        return $ticket->person;
    }

    /**
     * @param $ticket
     * @param $message
     * @param $feedback
     */
    private function updateTicketFeedbackRating($ticket, $message, $feedback)
    {
        $last_message_id = $this->getConn()->fetchColumn('
                    SELECT message_id FROM ticket_feedback
                    WHERE ticket_id = ?
                    ORDER BY message_id DESC
                    LIMIT 1
                ', [$ticket->getId()]);

        if (!$last_message_id || $message->getId() >= $last_message_id) {
            $ticket->feedback_rating = $feedback->getRating();
            $this->getEm()->persist($ticket);
        }
    }

    /**
     * @param $type
     * @param $person
     *
     * @return int|null
     */
    protected function getAwaitingUserCount($type, $person)
    {
        $ticket_data = $this->getTicketsDataService();

        if ('organization' === $type) {
            return $ticket_data->getOrganizationTicketCount($person, 'awaiting_user');
        }

        return $ticket_data->getTicketCount($person, 'awaiting_user');
    }

    /**
     * @param $ticket_ref
     * @param $auth
     * @param $_route
     *
     * @return Ticket|null
     */
    protected function getTicketForViewPage($ticket_ref, $auth, $_route)
    {
        // If a ticket is being viewed with "auth" then it uses different security. Anyone that is authenticated
        // can view a ticket with the /ticket-view/$auth route.

        // If it is not the auth route, the normal security applies via the /tickets/$ticket_ref route.

        if ($_route === 'portal_tickets_guest_view') {
            if (!$ticket = $this->getTicketByAuthIfGrantedAccess($auth)) {
                throw $this->createNotFoundException(sprintf('no ticket with auth "%s" found', $auth));
            }

            return $ticket;
        }

        // normal route, normal security
        if (!$ticket = $this->getTicketByRefOrId($ticket_ref)) {
            throw $this->createNotFoundException(sprintf('no ticket with ref or id "%s" found', $ticket_ref));
        }

        return $ticket;
    }

    /**
     * @param Ticket $ticket
     */
    protected function addCurrentUserAsParticipantIfTheyAreNot(Ticket $ticket)
    {
        // if the user is submitting a reply and are not a participant yet, we should add them
        // after we add them as a participant, the redirect below to the "normal" view page
        // will work, because they are now granted access to it.
        $person = $this->getCurrentPerson();
        if (!$ticket->isParticipant($person) && !$ticket->isOwner($person)) {
            $participant = new TicketParticipant();
            $participant->setPerson($person);
            $ticket->addParticipant($participant);
            $this->getEm()->persist($participant);
        }
    }
}
