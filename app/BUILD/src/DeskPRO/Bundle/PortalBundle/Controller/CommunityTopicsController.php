<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RelatedContent;
use Application\DeskPRO\Notifications\NewCommentNotification;
use Application\DeskPRO\Notifications\NewCommunityTopicNotification;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommunityTopicAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\NewCommunityTopicType;
use DeskPRO\Bundle\PortalBundle\Helper\CommunityFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use DeskPRO\Bundle\PortalBundle\Person\EmailValidationRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use DeskPRO\Component\Util\LazyPropObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class CommunityTopicsController.
 */
class CommunityTopicsController extends AbstractPublishController
{
    /**
     * @var array Used to cache response filter parameters
     */
    private static $filterParameters = [];

    /**
     * @Route("/community.{_format}", name="portal_community", defaults={"_format":"html"},
     *     requirements={"_format":"html|rss"})
     * @Route("/community", name="user_community_home")
     * @Security("is_granted('USE_COMMUNITY')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param string  $_format
     *
     * @throws \Exception
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getUser() ?: new PersonGuest();

        // RSS

        if ('rss' === $_format) {
            $filter = new CommunityFilter([
                'status'            => $request->query->get('status', 'all'),
                'status_categories' => $request->query->get('status_categories', []),
                'types'             => $request->query->get('types', []),
                'sort'              => $request->query->get('sort', 'date'),
                'sort_direction'    => $request->query->get('sort_direction', 'desc'),
            ]);

            $pager = $this->getCommunityDataService()->getItemsPager(
                $page,
                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $filter,
                $person
            );

            return $this->render('PortalBundle:Community:feed.rss.twig', [
                'pager'      => $pager,
                'category'   => null,
                'page_title' => $this->createPageTitle()->community(),
            ]);
        }
        $rssLink = $this->generateUrl('portal_community', ['_format' => 'rss']);

        // NEW COMMUNITY TOPIC FORM

        // true if auto-submit SavedFormController wants us to definitely rerender
        $rerenderingSaved  = $request->attributes->get('rerender-form', false);
        $permissionBag     = $this->getPermissionBagForCurrentUser();
        $newCommunityTopic = new CommunityTopic();
        $newCommunityTopic->setIsReviewed(false);
        if (!$permissionBag->hasPermission('community.no_submit_validate')) {
            $newCommunityTopic->setStatus(CommunityTopic::STATUS_HIDDEN);
        } else {
            $newCommunityTopic->setStatus(CommunityTopic::STATUS_ACTIVE);
            $newCommunityTopic->setStatusCategory($this->getDefaultStatusCategory());
        }
        $newCommunityTopic->setPerson($person);
        $form = $this->createForm(NewCommunityTopicType::class, $newCommunityTopic, [
            'person'                => $person,
            'action'                => $this->generateUrl('portal_community'),
            'saved_form_subrequest' => $request->attributes->has('saved-form'),
            // next to allow extra fields if its saved form because name/email etc will be on origin form,
            // but not this one now that the user is logged-in
            'allow_extra_fields' => $request->attributes->has('saved-form'),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->saveNewTopic(
                $form,
                $request,
                $newCommunityTopic,
                $rerenderingSaved,
                false,
                $person
            );
        }

        $formWasSubmitted = false;
        if ($form->isSubmitted()) {
            $formWasSubmitted = true;
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildCommunity();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.community_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('community', $this->getUser());
        }

        // FILTER FORUMS

        $communityForums = $this->get('data.community')->getCommunityForumsForPerson($person);
        $topicCountPerForum = $this->get('data.community')->getCommunityForumTopicCountsForPerson($person);
        $latestComments = $this->get('data.community')->getLatestCommentsPerForum($person);

        // JS INITIAL DATA

        $filter             = new CommunityFilter(); // get the defaults$allowed_types_parsed = array();
        $allowedTypesParsed = [];
        foreach ($communityForums as $cat) {
            $allowedTypesParsed[] = $cat->getId();
        }
        $filter->setTypes($allowedTypesParsed);
        $filterJs = $this->generateFilterJs($filter, $communityForums, $page);

        $check = $this->submitNewCommunityTopicAbuseCheck($person, $request->getClientIp(), false);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Community:index.html.twig',
            [
                'mode'                      => 'home',
                'page'                      => $page,
                'community_forums'          => $communityForums,
                'count'                     => $this->getBrandSetting('portal.per_page_content'),
                'show_pagination'           => true,
                'status'                    => $filter->getStatus(),
                'status_categories'         => $filter->getStatusCategories(),
                'types'                     => $filter->getTypes(),
                'sort'                      => $filter->getSort(),
                'sort_direction'            => $filter->getSortDirection(),
                'form'                      => $form->createView(),
                'form_was_submitted'        => $formWasSubmitted,
                'user'                      => $this->getUser(),
                'rerendering_saved'         => $rerenderingSaved,
                'breadcrumbs'               => $breadcrumbs,
                'page_title'                => $this->createPageTitle()->community(),
                'rss_link'                  => $rssLink,
                'filter_js'                 => $filterJs,
                'is_subscribed'             => $isSubscribed,
                'lockout'                   => $check->isLockoutRecommended(),
                'lockout_time'              => $check->getLockoutTime(true),
                'topic_count_per_forum'     => $topicCountPerForum,
                'latest_comments_per_forum' => $latestComments,
                'is_community_enabled'      => $this->isCommunityEnabled(),
            ]
        );
    }

    /**
     * @param CommunityTopic $newCommunityTopic
     * @param Person $person
     * @param Request $request
     *
     * @param bool $redirectToBrowseTopic
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function acceptNewCommunityTopic(
        CommunityTopic $newCommunityTopic,
        Person $person,
        Request $request,
        $redirectToBrowseTopic = false
    ) {
        $this->getEm()->persist($newCommunityTopic);
        $this->getEm()->flush();

        if ($newCommunityTopic->isVisibleOnPortal()) {
            $this->addFlash('success', $this->phrase('portal.flashes.new_community_topic_posted'));
            $destination = $this->getObjectRouter()->getPortalPath($newCommunityTopic);
        } else {
            $this->addFlash('success', $this->phrase('portal.flashes.new_community_topic_awaiting_review'));
            $destination = $this->generateUrl('portal_community');
        }

        $notify = new NewCommunityTopicNotification($newCommunityTopic);
        $notify->send();

        $this->getEmailSender()->sendNewCommunityTopicEmail($newCommunityTopic);

        $redirect = $this->get('portal_validation')->getPasswordRedirectIfRequired($person, $request, $destination);
        if ($redirect) {
            return $redirect;
        }

        if ($redirectToBrowseTopic) {
            $destination = $this->generateUrl('portal_community_browse', [
                'filter_uri' => sprintf('type-%d', $newCommunityTopic->getForum()->getId()),
            ]);
        }

        return $this->redirect($destination);
    }

    /**
     * @param mixed  $person
     * @param string $ip
     * @param bool   $withResponse
     *
     * @return SubmitCommunityTopicAbuseCheck
     */
    public function submitNewCommunityTopicAbuseCheck($person, $ip, $withResponse = true)
    {
        $check = new SubmitCommunityTopicAbuseCheck($person, $ip);
        if ($withResponse) {
            $check->setResponse($this->redirectToRoute('portal_community', ['lockout' => 'community']));
        } else {
            $check->markAsCheckOnly();
        }
        $this->getAntiAbuseService()->check($check);

        return $check;
    }

    /**
     * @Route("/community/browse/{filter_uri}", name="portal_community_browse", defaults={"query_path":""},
     *     requirements={"filter_uri":".*"})
     * @Method("GET")
     * @Security("is_granted('USE_COMMUNITY')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param $filter_uri
     *
     * @return Response
     */
    public function browseAction(Request $request, $filter_uri)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getUser() ?: new PersonGuest();

        try {
            $uriHelper = new CommunityFilterUriHelper();
            $filter    = $uriHelper->extractCommunityFilter($filter_uri);
            $filter->setQ($request->query->get('q', ''));
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('filter_uri could not be parsed');
        }

        // SECURITY
        // a permissions check, if the user can't see one of these filtered "types" (i.e. CommunityForum)
        $permissionsBag       = $this->getPermissionBag($person);
        $allowed_category_ids = $permissionsBag->getAllowedCommunityForumIds();
        foreach ($filter->getTypes() as $type) {
            if (!in_array($type, $allowed_category_ids)) {
                throw new AccessDeniedException(
                    'you dont have access to a category you are trying to filter for
                ');
            }
        }

        // order was incorrect, redirect, but only if this is not an ajax request
        $generatedUri = $uriHelper->generateUriSegment($filter);
        if (!$request->isXmlHttpRequest() && $filter_uri != $generatedUri) {
            if (strlen($generatedUri) < 1) {
                // actually, in this case, it is all the defaults, so go back to the index
                return $this->redirectToRoute(
                    'portal_community',
                    ['page' => $page]
                );
            }

            $context = [
                'filter_uri' => $generatedUri,
                'page'       => $page,
            ];

            if ($filter->getQ()) {
                $context['q'] = $filter->getQ();
            }

            return $this->redirectToRoute(
                'portal_community_browse',
                $context,
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildCommunity();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.community_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('community', $this->getUser());
        }

        // FILTER CATEGORIES

        $communityForums = $this->get('data.community')->getCommunityForumsForPerson($person);
        $filterJs        = $this->generateFilterJs($filter, $communityForums, $page, true);

        $pageOptions = [
            'mode'                 => 'browse',
            'page'                 => $page,
            'community_forums'     => $communityForums,
            'count'                => $this->getBrandSetting('portal.per_page_content'),
            'show_pagination'      => true,
            'status'               => $filter->getStatus(),
            'status_categories'    => $filter->getStatusCategories(),
            'types'                => $filter->getTypes(),
            'sort'                 => $filter->getSort(),
            'sort_direction'       => $filter->getSortDirection(),
            'view'                 => $filter->getView(),
            'view_mode'            => $filter->getViewMode(),
            'activities'           => $filter->getActivities(),
            'breadcrumbs'          => $breadcrumbs,
            'page_title'           => $this->createPageTitle()->community(),
            'filter_js'            => $filterJs,
            'rerendering_saved'    => false, // wont happen here because we always rerender on index
            'is_subscribed'        => $isSubscribed,
            'lockout'              => $request->get('lockout', false),
            'lockout_time'         => 0,
            'filter_params'        => $this->generateFilterJs($filter, $communityForums, $page, false),
            'is_community_enabled' => $this->isCommunityEnabled(),
        ];

        // If there is a single, current type selected
        $currentForum = $filter->getCurrentType()
            ? $this->getRepo(CommunityForum::class)->find($filter->getCurrentType())
            : null
        ;

        // Lazy load topics list
        $pageOptions['topics_list'] = new LazyPropObject([
            'view' => function () use ($filter) {
                return $filter->getView();
            },
            'is_compact' => function () use ($filter) {
                return ($filter->getViewMode() === CommunityFilter::VIEW_MODE_COMPACT);
            },
            'topics_data' => function () use ($page, $filter) {
                return $this->getCommunityDataService()->getFilteredTopicList([
                    'page'              => $page,
                    'count'             => $this->getBrandSetting('portal.per_page_content'),
                    'status'            => $filter->getStatus(),
                    'status_categories' => $filter->getStatusCategories(),
                    'types'             => $filter->getTypes(),
                    'sort'              => $filter->getSort(),
                    'sort_direction'    => $filter->getSortDirection(),
                    'view'              => $filter->getView(),
                    'q'                 => $filter->getQ(),
                    'activities'        => $filter->getActivities(),
                    'view_mode'         => $filter->getViewMode(),
                ], $this->getUser());
            },
        ]);

        if ($request->isXmlHttpRequest()) {
            return $this->renderThemeView(
                'Theme:Community:items_ajax_partial.html.twig',
                $pageOptions
            );
        }

        // setup and render an initial form that posts to /community
        $person            = $this->getUser() ?: new PersonGuest();
        $newCommunityTopic = new CommunityTopic();
        $newCommunityTopic->setPerson($person);
        $form = $this->createForm(NewCommunityTopicType::class, $newCommunityTopic, [
            'person' => $person,
            'action' => $this->generateUrl('portal_community'),
        ]);

        $pageOptions = array_merge($pageOptions, [
            'form'               => $form->createView(),
            'form_was_submitted' => false,
            'user'               => $this->getUser(),
            'lockout'            => false,
            'lockout_time'       => false,
            'current_forum'      => $currentForum,
        ]);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Community:index.html.twig',
            $pageOptions
        );
    }

    /**
     * @Route("/community/{id}/create-topic", name="portal_community_topic_create")
     * @Method({"GET","POST"})
     * @Security("is_granted('USE_COMMUNITY')")
     *
     * @param CommunityForum $forum
     * @param Request $request
     */
    public function createTopicAction(CommunityForum $forum, Request $request)
    {
        $person            = $this->getUser() ?: new PersonGuest();
        $newCommunityTopic = new CommunityTopic();

        $newCommunityTopic->setPerson($person);
        $newCommunityTopic->setForum($forum);

        $form = $this->createForm(NewCommunityTopicType::class, $newCommunityTopic, [
            'person'              => $person,
            'has_forum_selection' => false,
            'action'              => $this->generateUrl('portal_community_topic_create', [
                'id' => $forum->getId(),
            ]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->saveNewTopic(
                $form,
                $request,
                $newCommunityTopic,
                false,
                true,
                $person
            );
        }

        return $this->renderThemeView('Theme:Community:create-topic.html.twig', [
            'form'        => $form->createView(),
            'breadcrumbs' => $this->getBreadcrumbGenerator()->buildCommunityCreate($forum),
        ]);
    }

    /**
     * @Route("/community/view/{slug}", name="portal_community_topic_view")
     * @Route("/community/view/{slug}", name="user_community_topic_view")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Security("is_granted('USE_COMMUNITY') and is_granted('VIEW_COMMUNITY', topic)")
     * @PageHttpCache(content="topic")
     *
     * @param Request        $request
     * @param CommunityTopic $topic
     * @param string         $visitor_id
     *
     * @return Response
     */
    public function viewAction(Request $request, CommunityTopic $topic, $visitor_id)
    {
        if (!$topic->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this community topic is hidden');
        }

        // COMMENT FORM

        $newCommentForm = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_COMMUNITY, $topic) && !$topic->isClosed()) {
            $formHandler = $this->get('form_handler.comment');
            $comment     = new CommunityTopicComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $formResult     = $formHandler->handle($newCommentForm, $request, $topic, $comment);
            if ($formResult) {
                $notify = new NewCommentNotification($comment);
                $notify->send();
            }
            if ($formResult instanceof Response) {
                return $formResult;
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildCommunityView($topic);

        // RATING

        $rating          = $this->findContentRating($topic, $visitor_id);
        $topic->can_rate = $this->isGranted(ContentRatingsVoter::RATE_COMMUNITY, $topic);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($topic);

        // SUBSCRIPTION

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.community_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_COMMUNITY, $topic)
        ) {
            // waiting on info on the kb subs
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedContent($topic, $this->getUser());
        }

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // REGISTERED PAGE VIEW LOG
        if ($person = $this->getUser()) {
            $this->container->get('content.page_view')->pageView($person, PageViewLog::TYPE_COMMUNITY, $topic->getId());
        }

        // RENDER THEME

        $communityTopicLinksRepo    = $this->get('doctrine.orm.default_entity_manager')->getRepository(TicketCommunityTopicLink::class);
        $ticketCommunityTopicsLinks = $communityTopicLinksRepo->findByTopic($topic);

        $self = $this;

        $ticketCommunityTopicsLinks = array_filter($ticketCommunityTopicsLinks, function ($linkedTicket) use ($self) {
            /* @var TicketCommunityTopicLink $linkedTicket */
            return $self->isGranted(TicketsVoter::TICKET_VIEW, $linkedTicket->getTicket());
        });

        $viewVars = [
            'topic'              => $topic,
            'linked_tickets'     => $ticketCommunityTopicsLinks,
            'is_subscribed'      => $isSubscribed,
            'content_id'         => $topic->getId(),
            'content_type'       => CommunityTopic::CONTENT_TYPE,
            'new_comment_form'   => $newCommentForm ? $newCommentForm->createView() : null,
            'page_title'         => $this->createPageTitle()->community($topic),
            'breadcrumbs'        => $breadcrumbs,
            'rating'             => $rating,
            'show_rating_counts' => $showRatingCounts,
            'rating_counts'      => $ratingCounts,
            'lockout'            => $check->isLockoutRecommended(),
            'lockout_time'       => $check->getLockoutTime(true),
        ];

        // Lazy load related content
        $viewVars['related_content_data'] = new LazyPropObject([
            'related_content' => function () use ($topic) {
                $relatedFinder = new RelatedContentFinder($this->getCurrentPerson(), $topic);

                return $relatedFinder->getRelatedEntities(true);
            },
        ]);

        if (!$this->getUser() || $this->getUser()->getId()) {
            $viewVars = array_merge($viewVars, $this->getAuthComponents($request));
        }

        return $this->renderThemeView(
            'Theme:Community:view.html.twig',
            $viewVars
        );
    }

    /**
     * @Route("/community/view/{slug}/vote-up", name="portal_community_topic_vote_up", defaults={"up_or_down":"up"})
     * @Route("/community/view/{slug}/vote-down", name="portal_community_topic_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @AutoPostOnGetRequest()
     *
     * @param Request        $request
     * @param CommunityTopic $topic
     * @param string         $visitor_id
     * @param string         $up_or_down
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|JsonResponse
     */
    public function communityRateAction(Request $request, CommunityTopic $topic, $visitor_id, $up_or_down)
    {
        if (!$this->isGranted('USE_COMMUNITY')) {
            throw $this->createAccessDeniedException($this->phrase('portal.community.module_forbidden'));
        }
        if (!$this->isGranted('RATE_COMMUNITY', $topic)) {
            if ($this->getUser()) {
                throw $this->createAccessDeniedException($this->phrase('portal.community.rate_forbidden'));
            }
            if ($request->getContentType() == 'json') {
                return new JsonResponse(
                    [
                        'error'    => $this->phrase('portal.community.error_login'),
                        'redirect' => $this->generateUrl('portal_login', [
                            '_destination' => $this->generateUrl('portal_community_topic_view', ['slug' => $topic->getSlug()]),
                        ]),
                    ]
                );
            } else {
                $this->addFlash('notice', $this->phrase('portal.flashes.community_login'));

                return $this->redirectToRoute('portal_login', ['_destination' => $this->generateUrl('portal_community_topic_view', ['slug' => $topic->getSlug()])]);
            }
        }
        if (!$topic->isVisibleOnPortal()) {
            throw $this->createNotFoundException($this->phrase('portal.community.error_hidden'));
        }

        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($topic, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($topic, $visitor_id, $person);
        }

        if ($request->getContentType() == 'json') {
            return new JsonResponse([
                'success' => true,
            ]);
        } else {
            $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

            return $this->redirectToRoute('portal_community_topic_view', ['slug' => $topic->getSlug()]);
        }
    }

    /**
     * @Route("/community/view/{slug}/toggle-subscription", name="portal_community_topic_toggle_subscription")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Security("is_granted('USE_COMMUNITY') and is_granted('SUBSCRIBE_COMMUNITY', topic)")
     * @AutoPostOnGetRequest()
     *
     * @param CommunityTopic $topic
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleSubscriptionAction(CommunityTopic $topic)
    {
        if (!$topic->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this community topic is hidden');
        }

        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedContent($topic, $person)) {
            $subscriptionsHelper->unsubscribeFromContent($topic, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.community_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToContent($topic, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.community_subscribe'));
        }

        return $this->redirectToRoute('portal_community_topic_view', ['slug' => $topic->getSlug()]);
    }

    /**
     * @Route("/community/root/toggle-subscription", name="portal_community_root_toggle_subscription")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_COMMUNITY')")
     * @AutoPostOnGetRequest()
     * @param Request $request
     * @return RedirectResponse
     */
    public function communityRootForumSubscriptionAction(Request $request)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedRootCategory('community', $person)) {
            $subscriptionsHelper->unsubscribeFromRootCategory('community', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('community', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_subscribe'));
        }

        if ($request->query->get('target')) {
            return $this->redirect($request->query->get('target'));
        }

        return $this->redirectToRoute('portal_community');
    }

    /**
     * @Route("/community/items/subscriptions/unsubscribe", name="portal_community_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_COMMUNITY')")
     * @AutoPostOnGetRequest()
     */
    public function communityUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('community', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.community_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @return \Application\DeskPRO\Entity\CommunityTopicStatusCategory
     */
    protected function getDefaultStatusCategory()
    {
        $communityDataService  = $this->getCommunityDataService();
        $defaultStatusCategory = $communityDataService->getCommunityFirstStatusCategoryByType(
            CommunityTopicStatusCategory::STATUS_ACTIVE
        );

        return $defaultStatusCategory;
    }

    /**
     * @param CommunityFilter $filter
     * @param array $communityForums
     *
     * @param $page
     * @param bool $isEncoded
     * @return string|array
     */
    public function generateFilterJs(CommunityFilter $filter, array $communityForums, $page, $isEncoded = true)
    {
        if (isset(self::$filterParameters[(int) $isEncoded])) {
            return self::$filterParameters[(int) $isEncoded];
        }

        $allowedTypesParsed = [];
        foreach ($communityForums as $cat) {
            $allowedTypesParsed[$cat->getId()] = $this->objectPhrase($cat);
        }

        $statusCategories       = [];
        $statusCategoriesEntity = $this->getRepo(CommunityTopicStatusCategory::class)->findBy(
            ['status_type' => CommunityFilter::$statuses]
        );

        /** @var CommunityTopicStatusCategory $statusCategory */
        foreach ($statusCategoriesEntity as $statusCategory) {
            $statusType = $statusCategory->getStatusType();
            if (!array_key_exists($statusType, $statusCategories)) {
                $statusCategories[$statusType] = [];
            }

            $statusCategories[$statusType][] = [
                'id'    => $statusCategory->getId(),
                'title' => $this->objectPhrase($statusCategory),
                'color' => $statusCategory->getColor(),
            ];
        }

        $theArray = [
            'filter'    => array_merge($filter->toArray(), ['page' => $page]),
            'available' => [
                'views'             => $this->transArray(CommunityFilter::$views_translated),
                'status'            => $this->transArray(CommunityFilter::$statuses_translated),
                'status_categories' => $statusCategories,
                'types'             => $allowedTypesParsed,
                'sorts'             => $this->transArray(CommunityFilter::$sorts_translated),
                'sort_directions'   => $this->transArray(CommunityFilter::$sort_directions_translated),
                'activities'        => $this->transArray(CommunityFilter::$activities_translated),
            ],
        ];

        if (!$isEncoded) {
            return self::$filterParameters[(int) $isEncoded] = $theArray;
        }

        $filterJs = json_encode(
            $theArray,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_NUMERIC_CHECK
        );

        return self::$filterParameters[(int) $isEncoded] = $filterJs;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function transArray(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $phrase) {
            $newArray[$key] = $this->phrase($phrase);
        }

        return $newArray;
    }

    public function redirectCommunityAction($url)
    {
        return $this->redirect('/community/'.$url, RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param CommunityTopic $newCommunityTopic
     * @param bool $rerenderingSaved
     * @param bool $redirectToBrowseTopic
     * @param Person|null $person
     * @return RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveNewTopic(
        FormInterface $form,
        Request $request,
        CommunityTopic $newCommunityTopic,
        $rerenderingSaved,
        $redirectToBrowseTopic,
        Person $person = null
    ) {
        if (
            !$rerenderingSaved // if we are rerendering dont pass this condition
            &&
            (
                !$form->getClickedButton() // if user clicked more_attachments dont pass condition
                ||
                (
                    $form->getClickedButton()
                    && $form->getClickedButton()->getConfig()->getName() !== 'more_attachments'
                )
            )
        ) {
            // deal with guests via negotiating with PersonFactory
            if ($person instanceof PersonGuest) {
                try {
                    $this->getPersonFactory()->checkGuestForValidation($person, $request->attributes->get('saved-form'));

                    // the below block only executes during a saved form request (they clicked validation link)
                    $email  = $person->getPrimaryEmail();
                    $person = $this->getPersonDataService()->getPersonForEmail($email->getEmail());

                    // since the guest is set on the form, we need to update all of the associations
                    $newCommunityTopic->setPerson($person);
                    foreach ($newCommunityTopic->getAttachments() as $attachment) {
                        $attachment->setPerson($person);
                    }

                    return $this->acceptNewCommunityTopic($newCommunityTopic, $person, $request, $redirectToBrowseTopic);
                } catch (LoginRequiredException $e) {
                    $person = $e->getPerson();
                    $this->submitNewCommunityTopicAbuseCheck($person, $request->getClientIp());

                    if ($person instanceof PersonGuest) {
                        $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_COMMUNITY_TOPIC, $form, $request, $person->getEmail(), $person->getDisplayName());

                        return new RedirectResponse(
                            $this->container->get('router')->generate('portal_login', [
                                'saved_form' => $savedForm->getExternalCode(),
                            ])
                        );
                    } else {
                        return $this->getFormSaver()->saveFormForPersonLogin(SavedForm::TYPE_NEW_COMMUNITY_TOPIC, $person, $form, $request);
                    }
                } catch (EmailValidationRequiredException $e) {
                    $this->submitNewCommunityTopicAbuseCheck($person, $request->getClientIp());

                    $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_COMMUNITY_TOPIC, $form, $request, $person->getEmailAddress(), $person->getDisplayName());
                    $this->get('portal_validation')->sendVerificationEmail(PortalValidation::NEW_COMMUNITY_TOPIC, $savedForm);
                    $this->addFlash('success', $this->phrase('portal.flashes.guest_content_must_verify'));

                    return $this->redirectToRoute('portal_community');
                }
            }

            $this->submitNewCommunityTopicAbuseCheck($person, $request->getClientIp());

            return $this->acceptNewCommunityTopic($newCommunityTopic, $person, $request, $redirectToBrowseTopic);
        }
    }
}
