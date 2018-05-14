<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Notifications\NewCommentNotification;
use Application\DeskPRO\Notifications\NewFeedbackNotification;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitFeedbackAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\NewFeedbackType;
use DeskPRO\Bundle\PortalBundle\Helper\FeedbackFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\Helper\PortalValidation;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use DeskPRO\Bundle\PortalBundle\Person\EmailValidationRequiredException;
use DeskPRO\Bundle\PortalBundle\Person\LoginRequiredException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FeedbackController.
 */
class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback.{_format}", name="portal_feedback", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/feedback", name="user_feedback_home")
     * @Security("is_granted('USE_FEEDBACK')")
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
            $filter = new FeedbackFilter([
                'status'            => $request->query->get('status', 'all'),
                'status_categories' => $request->query->get('status_categories', []),
                'types'             => $request->query->get('types', []),
                'sort'              => $request->query->get('sort', 'date'),
                'sort_direction'    => $request->query->get('sort_direction', 'desc'),
            ]);

            $pager = $this->getFeedbackDataService()->getItemsPager(
                $page,
                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $filter,
                $person
            );

            return $this->render('PortalBundle:Feedback:feed.rss.twig', [
                'pager'      => $pager,
                'category'   => null,
                'page_title' => $this->createPageTitle()->feedback(),
            ]);
        }
        $rssLink = $this->generateUrl('portal_feedback', ['_format' => 'rss']);

        // NEW FEEDBACK FORM

        // true if auto-submit SavedFormController wants us to definitely rerender
        $rerenderingSaved = $request->attributes->get('rerender-form', false);
        $permissionBag    = $this->getPermissionBagForCurrentUser();
        $newFeedback      = new Feedback();
        $newFeedback->setIsReviewed(false);
        if (!$permissionBag->hasPermission('feedback.no_submit_validate')) {
            $newFeedback->setStatus(Feedback::STATUS_HIDDEN);
        } else {
            $newFeedback->setStatus(Feedback::STATUS_ACTIVE);
            $newFeedback->setStatusCategory($this->getDefaultStatusCategory());
        }
        $newFeedback->setPerson($person);
        $form = $this->createForm(NewFeedbackType::class, $newFeedback, [
            'person'                => $person,
            'action'                => $this->generateUrl('portal_feedback'),
            'saved_form_subrequest' => $request->attributes->has('saved-form'),
            // next to allow extra fields if its saved form because name/email etc will be on origin form,
            // but not this one now that the user is logged-in
            'allow_extra_fields' => $request->attributes->has('saved-form'),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
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
                        $newFeedback->setPerson($person);
                        foreach ($newFeedback->getAttachments() as $attachment) {
                            $attachment->setPerson($person);
                        }

                        return $this->acceptNewFeedback($newFeedback, $person, $request);
                    } catch (LoginRequiredException $e) {
                        $person = $e->getPerson();
                        $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                        if ($person instanceof PersonGuest) {
                            $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_FEEDBACK, $form, $request, $person->getEmail(), $person->getDisplayName());

                            return new RedirectResponse(
                                $this->container->get('router')->generate('portal_login', [
                                    'saved_form' => $savedForm->getExternalCode(),
                                ])
                            );
                        } else {
                            return $this->getFormSaver()->saveFormForPersonLogin(SavedForm::TYPE_NEW_FEEDBACK, $person, $form, $request);
                        }
                    } catch (EmailValidationRequiredException $e) {
                        $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                        $savedForm = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_FEEDBACK, $form, $request, $person->getEmailAddress(), $person->getDisplayName());
                        $this->get('portal_validation')->sendVerificationEmail(PortalValidation::NEW_FEEDBACK, $savedForm);
                        $this->addFlash('success', $this->phrase('portal.flashes.guest_content_must_verify'));

                        return $this->redirectToRoute('portal_feedback');
                    }
                }

                $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                return $this->acceptNewFeedback($newFeedback, $person, $request);
            }
        }

        $formWasSubmitted = false;
        if ($form->isSubmitted()) {
            $formWasSubmitted = true;
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedback();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.feedback_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('feedback', $this->getUser());
        }

        // FILTER CATEGORIES

        $feedbackTypes = $this->get('data.feedback')->getFeedbackCategoriesForPerson($person);

        // JS INITIAL DATA

        $filter             = new FeedbackFilter(); // get the defaults$allowed_types_parsed = array();
        $allowedTypesParsed = [];
        foreach ($feedbackTypes as $cat) {
            $allowedTypesParsed[] = $cat->getId();
        }
        $filter->setTypes($allowedTypesParsed);
        $filterJs = $this->generateFilterJs($filter, $feedbackTypes, $page);

        $check = $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp(), false);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            [
                'page'               => $page,
                'feedback_types'     => $feedbackTypes,
                'count'              => $this->getBrandSetting('portal.per_page_content'),
                'show_pagination'    => true,
                'status'             => $filter->getStatus(),
                'status_categories'  => $filter->getStatusCategories(),
                'types'              => $filter->getTypes(),
                'sort'               => $filter->getSort(),
                'sort_direction'     => $filter->getSortDirection(),
                'form'               => $form->createView(),
                'form_was_submitted' => $formWasSubmitted,
                'user'               => $this->getUser(),
                'rerendering_saved'  => $rerenderingSaved,
                'breadcrumbs'        => $breadcrumbs,
                'page_title'         => $this->createPageTitle()->feedback(),
                'rss_link'           => $rssLink,
                'filter_js'          => $filterJs,
                'is_subscribed'      => $isSubscribed,
                'lockout'            => $check->isLockoutRecommended(),
                'lockout_time'       => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * @param Feedback $newFeedback
     * @param Person   $person
     * @param Request  $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function acceptNewFeedback(Feedback $newFeedback, Person $person, Request $request)
    {
        $this->getEm()->persist($newFeedback);
        $this->getEm()->flush();

        if ($newFeedback->isVisibleOnPortal()) {
            $this->addFlash('success', $this->phrase('portal.flashes.new_feedback_posted'));
            $destination = $this->getObjectRouter()->getPortalPath($newFeedback);
        } else {
            $this->addFlash('success', $this->phrase('portal.flashes.new_feedback_awaiting_review'));
            $destination = $this->generateUrl('portal_feedback');
        }

        $notify = new NewFeedbackNotification($newFeedback);
        $notify->send();

        $this->getEmailSender()->sendNewFeedbackEmail($newFeedback);

        $redirect = $this->get('portal_validation')->getPasswordRedirectIfRequired($person, $request, $destination);
        if ($redirect) {
            return $redirect;
        }

        return $this->redirect($destination);
    }

    /**
     * @param mixed  $person
     * @param string $ip
     * @param bool   $withResponse
     *
     * @return SubmitFeedbackAbuseCheck
     */
    public function submitNewFeedbackAbuseCheck($person, $ip, $withResponse = true)
    {
        $check = new SubmitFeedbackAbuseCheck($person, $ip);
        if ($withResponse) {
            $check->setResponse($this->redirectToRoute('portal_feedback', ['lockout' => 'feedback']));
        } else {
            $check->markAsCheckOnly();
        }
        $this->getAntiAbuseService()->check($check);

        return $check;
    }

    /**
     * @Route("/feedback/browse/{filter_uri}", name="portal_feedback_browse", defaults={"query_path":""}, requirements={"filter_uri":".*"})
     * @Method("GET")
     * @Security("is_granted('USE_FEEDBACK')")
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
            $uriHelper = new FeedbackFilterUriHelper();
            $filter    = $uriHelper->extractFeedbackFilter($filter_uri);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('filter_uri could not be parsed');
        }

        // SECURITY
        // a permissions check, if the user can't see one of these filtered "types" (i.e. FeedbackCategory)
        $permissionsBag       = $this->getPermissionBag($person);
        $allowed_category_ids = $permissionsBag->getAllowedFeedbackCategoryIds();
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
                    'portal_feedback',
                    ['page' => $page]
                );
            }

            return $this->redirectToRoute('portal_feedback_browse', [
                'filter_uri' => $generatedUri,
                'page'       => $page,
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedback();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.feedback_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('feedback', $this->getUser());
        }

        // FILTER CATEGORIES

        $feedbackTypes = $this->get('data.feedback')->getFeedbackCategoriesForPerson($person);
        $filterJs      = $this->generateFilterJs($filter, $feedbackTypes, $page);

        $pageOptions = [
            'page'              => $page,
            'feedback_types'    => $feedbackTypes,
            'count'             => $this->getBrandSetting('portal.per_page_content'),
            'show_pagination'   => true,
            'status'            => $filter->getStatus(),
            'status_categories' => $filter->getStatusCategories(),
            'types'             => $filter->getTypes(),
            'sort'              => $filter->getSort(),
            'sort_direction'    => $filter->getSortDirection(),
            'breadcrumbs'       => $breadcrumbs,
            'page_title'        => $this->createPageTitle()->feedback(),
            'filter_js'         => $filterJs,
            'rerendering_saved' => false, // wont happen here because we always rerender on index
            'is_subscribed'     => $isSubscribed,
            'lockout'           => $request->get('lockout', false),
            'lockout_time'      => 0,
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->renderThemeView(
                'Theme:Feedback:items_ajax_partial.html.twig',
                $pageOptions
            );
        }

        // setup and render an initial form that posts to /feedback
        $person      = $this->getUser() ?: new PersonGuest();
        $newFeedback = new Feedback();
        $newFeedback->setPerson($person);
        $form = $this->createForm(NewFeedbackType::class, $newFeedback, [
            'person' => $person,
            'action' => $this->generateUrl('portal_feedback'),
        ]);

        $pageOptions = array_merge($pageOptions, [
            'form'               => $form->createView(),
            'form_was_submitted' => false,
            'user'               => $this->getUser(),
            'lockout'            => false,
            'lockout_time'       => false,
        ]);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            $pageOptions
        );
    }

    /**
     * @Route("/feedback/view/{slug}", name="portal_feedback_view")
     * @Route("/feedback/view/{slug}", name="user_feedback_view")
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK') and is_granted('VIEW_FEEDBACK', item)")
     * @PageHttpCache(content="item")
     *
     * @param Request  $request
     * @param Feedback $item
     * @param string   $visitor_id
     *
     * @return Response
     */
    public function viewAction(Request $request, Feedback $item, $visitor_id)
    {
        if (!$item->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this feedback item is hidden');
        }

        // COMMENT FORM

        $newCommentForm = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_FEEDBACK, $item)) {
            $formHandler = $this->get('form_handler.comment');
            $comment     = new FeedbackComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $formResult     = $formHandler->handle($newCommentForm, $request, $item, $comment);
            if ($formResult) {
                $notify = new NewCommentNotification($comment);
                $notify->send();
            }
            if ($formResult instanceof Response) {
                return $formResult;
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedbackView($item);

        // RATING

        $rating         = $this->findContentRating($item, $visitor_id);
        $item->can_rate = $this->isGranted(ContentRatingsVoter::RATE_FEEDBACK, $item);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($item);

        // SUBSCRIPTION

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.feedback_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_FEEDBACK, $item)
        ) {
            // waiting on info on the kb subs
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedContent($item, $this->getUser());
        }

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // REGISTERED PAGE VIEW LOG
        if ($person = $this->getUser()) {
            $this->container->get('content.page_view')->pageView($person, PageViewLog::TYPE_FEEDBACK, $item->getId());
        }

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Feedback:view.html.twig',
            [
                'item'               => $item,
                'is_subscribed'      => $isSubscribed,
                'content_id'         => $item->getId(),
                'content_type'       => Feedback::CONTENT_TYPE,
                'new_comment_form'   => $newCommentForm ? $newCommentForm->createView() : null,
                'page_title'         => $this->createPageTitle()->feedback($item),
                'breadcrumbs'        => $breadcrumbs,
                'rating'             => $rating,
                'show_rating_counts' => $showRatingCounts,
                'rating_counts'      => $ratingCounts,
                'lockout'            => $check->isLockoutRecommended(),
                'lockout_time'       => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * @Route("/feedback/view/{slug}/vote-up", name="portal_feedback_vote_up", defaults={"up_or_down":"up"})
     * @Route("/feedback/view/{slug}/vote-down", name="portal_feedback_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @AutoPostOnGetRequest()
     *
     * @param Request  $request
     * @param Feedback $item
     * @param string   $visitor_id
     * @param string   $up_or_down
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|JsonResponse
     */
    public function feedbackRateAction(Request $request, Feedback $item, $visitor_id, $up_or_down)
    {
        if (!$this->isGranted('USE_FEEDBACK')) {
            throw $this->createAccessDeniedException($this->phrase('portal.feedback.module_forbidden'));
        }
        if (!$this->isGranted('RATE_FEEDBACK', $item)) {
            if ($this->getUser()) {
                throw $this->createAccessDeniedException($this->phrase('portal.feedback.rate_forbidden'));
            }
            if ($request->getContentType() == 'json') {
                return new JsonResponse(
                    [
                        'error'    => $this->phrase('portal.feedback.error_login'),
                        'redirect' => $this->generateUrl('portal_login', [
                            '_destination' => $this->generateUrl('portal_feedback_view', ['slug' => $item->getSlug()]),
                        ]),
                    ]
                );
            } else {
                $this->addFlash('notice', $this->phrase('portal.flashes.feedback_login'));

                return $this->redirectToRoute('portal_login', ['_destination' => $this->generateUrl('portal_feedback_view', ['slug' => $item->getSlug()])]);
            }
        }
        if (!$item->isVisibleOnPortal()) {
            throw $this->createNotFoundException($this->phrase('portal.feedback.error_hidden'));
        }

        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($item, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($item, $visitor_id, $person);
        }

        if ($request->getContentType() == 'json') {
            return new JsonResponse([
                'success' => true,
            ]);
        } else {
            $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

            return $this->redirectToRoute('portal_feedback_view', ['slug' => $item->getSlug()]);
        }
    }

    /**
     * @Route("/feedback/view/{slug}/toggle-subscription", name="portal_feedback_toggle_subscription")
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK') and is_granted('SUBSCRIBE_FEEDBACK', item)")
     * @AutoPostOnGetRequest()
     *
     * @param Feedback $item
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleSubscriptionAction(Feedback $item)
    {
        if (!$item->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this feedback item is hidden');
        }

        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedContent($item, $person)) {
            $subscriptionsHelper->unsubscribeFromContent($item, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.feedback_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToContent($item, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.feedback_subscribe'));
        }

        return $this->redirectToRoute('portal_feedback_view', ['slug' => $item->getSlug()]);
    }

    /**
     * @Route("/feedback/root/toggle-subscription", name="portal_feedback_root_toggle_subscription")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_FEEDBACK')")
     * @AutoPostOnGetRequest()
     */
    public function articleRootCategorySubscriptionAction()
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedRootCategory('feedback', $person)) {
            $subscriptionsHelper->unsubscribeFromRootCategory('feedback', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('feedback', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_feedback');
    }

    /**
     * @Route("/feedback/items/subscriptions/unsubscribe", name="portal_feedback_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_FEEDBACK')")
     * @AutoPostOnGetRequest()
     */
    public function feedbackUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('feedback', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.feedback_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @return \Application\DeskPRO\Entity\FeedbackStatusCategory
     */
    protected function getDefaultStatusCategory()
    {
        $feedbackDataService   = $this->getFeedbackDataService();
        $defaultStatusCategory = $feedbackDataService->getFeedbackFirstStatusCategoryByType(
            FeedbackStatusCategory::STATUS_ACTIVE
        );

        return $defaultStatusCategory;
    }

    /**
     * @param $filter
     * @param $feedbackTypes
     *
     * @return string
     */
    public function generateFilterJs(FeedbackFilter $filter, array $feedbackTypes, $page)
    {
        $allowedTypesParsed = [];
        foreach ($feedbackTypes as $cat) {
            $allowedTypesParsed[$cat->getId()] = $this->objectPhrase($cat);
        }

        $statusCategories       = [];
        $statusCategoriesEntity = $this->getRepo('DeskPRO:FeedbackStatusCategory')->findBy(
            ['status_type' => FeedbackFilter::$statuses]
        );
        foreach ($statusCategoriesEntity as $statusCategory) {
            $statusType = $statusCategory->getStatusType();
            if (!array_key_exists($statusType, $statusCategories)) {
                $statusCategories[$statusType] = [];
            }

            $statusCategories[$statusType][] = [
                'id'    => $statusCategory->getId(),
                'title' => $this->objectPhrase($statusCategory),
            ];
        }

        $theArray = [
            'filter'    => array_merge($filter->toArray(), ['page' => $page]),
            'available' => [
                'status'            => $this->transArray(FeedbackFilter::$statuses_translated),
                'status_categories' => $statusCategories,
                'types'             => $allowedTypesParsed,
                'sorts'             => $this->transArray(FeedbackFilter::$sorts_translated),
                'sort_directions'   => $this->transArray(FeedbackFilter::$sort_directions_translated),
            ],
        ];

        $filterJs = json_encode(
            $theArray,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_NUMERIC_CHECK
        );

        return $filterJs;
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
}
