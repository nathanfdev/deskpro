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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitFeedbackAbuseCheck;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
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
     * @return Response
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->query->get('page', 1);
        $person = $this->getUser() ?: new PersonGuest();

        //
        // RSS
        //
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
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $filter,
                $person
            );

            return $this->render('PortalBundle:Feedback:feed.rss.twig', [
                'pager'      => $pager,
                'category'   => null,
                'page_title' => $this->createPageTitle()->feedback(),
            ]);
        }
        $rss_link = $this->generateUrl('portal_feedback', ['_format' => 'rss']);

        //
        // NEW FEEDBACK FORM
        //
        $rerendering_saved = $request->attributes->get('rerender-form', false); // true if auto-submit SavedFormController wants us to definitely rerender
        $permission_bad    = $this->getPermissionBagForCurrentUser();
        $new_feedback      = new Feedback();
        $new_feedback->setIsReviewed(false);
        if (!$permission_bad->hasPermission('feedback.no_submit_validate')) {
            $new_feedback->setStatus(Feedback::STATUS_HIDDEN);
        } else {
            $new_feedback->setStatus(Feedback::STATUS_ACTIVE);
        }
        $new_feedback->setStatusCategory($this->getDefaultStatusCategory());
        $new_feedback->setPerson($person);
        $form = $this->createForm('new_feedback', $new_feedback, [
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
                !$rerendering_saved // if we are rerendering dont pass this condition
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
                        $new_feedback->setPerson($person);
                        foreach ($new_feedback->getAttachments() as $attachment) {
                            $attachment->setPerson($person);
                        }

                        return $this->acceptNewFeedback($new_feedback, $person, $request);
                    } catch (LoginRequiredException $e) {
                        $person = $e->getPerson();
                        $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                        return $this->getFormSaver()->saveFormForPersonLogin(SavedForm::TYPE_NEW_FEEDBACK, $person, $form, $request);
                    } catch (EmailValidationRequiredException $e) {
                        $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                        $saved_form = $this->getFormSaver()->saveForm(SavedForm::TYPE_NEW_FEEDBACK, $form, $request, $person->getEmailAddress(), $person->getDisplayName());
                        $this->get('portal_validation')->sendVerificationEmail(PortalValidation::NEW_FEEDBACK, $saved_form);
                        $this->addFlash('success', $this->phrase('portal.flashes.guest_content_must_verify'));

                        return $this->redirectToRoute('portal_feedback');
                    }
                }

                $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());

                return $this->acceptNewFeedback($new_feedback, $person, $request);
            }
        } elseif ($form->isSubmitted()) {
            $this->submitNewFeedbackAbuseCheck($person, $request->getClientIp());
        }

        $form_was_submitted = false;
        if ($form->isSubmitted()) {
            $form_was_submitted = true;
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedback();

        //
        // FILTER CATEGORIES
        //
        $feedback_types = $this->get('data.feedback')->getFeedbackCategoriesForPerson($person);

        //
        // JS INITIAL DATA
        //
        $filter               = new FeedbackFilter(); // get the defaults$allowed_types_parsed = array();
        $allowed_types_parsed = [];
        foreach ($feedback_types as $cat) {
            $allowed_types_parsed[] = $cat->getId();
        }
        $filter->setTypes($allowed_types_parsed);
        $filter_js = $this->generateFilterJs($filter, $feedback_types, $page);

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            [
                'page'               => $page,
                'feedback_types'     => $feedback_types,
                'count'              => $this->getBrandSetting('portal.per_page_content'),
                'show_pagination'    => true,
                'status'             => $filter->getStatus(),
                'status_categories'  => $filter->getStatusCategories(),
                'types'              => $filter->getTypes(),
                'sort'               => $filter->getSort(),
                'sort_direction'     => $filter->getSortDirection(),
                'form'               => $form->createView(),
                'form_was_submitted' => $form_was_submitted,
                'user'               => $this->getUser(),
                'rerendering_saved'  => $rerendering_saved,
                'breadcrumbs'        => $breadcrumbs,
                'page_title'         => $this->createPageTitle()->feedback(),
                'rss_link'           => $rss_link,
                'filter_js'          => $filter_js,
            ]
        );
    }

    /**
     * @param Feedback $new_feedback
     * @param Person   $person
     * @param Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function acceptNewFeedback(Feedback $new_feedback, Person $person, Request $request)
    {
        $this->persistAndFlushEntity($new_feedback);

        if ($new_feedback->isVisibleOnPortal()) {
            $this->addFlash('success', $this->phrase('portal.flashes.new_feedback_posted'));
            $destination = $this->getObjectRouter()->getPortalPath($new_feedback);
        } else {
            $this->addFlash('success', $this->phrase('portal.flashes.new_feedback_awaiting_review'));
            $destination = $this->generateUrl('portal_feedback');
        }

        $this->getEmailSender()->sendNewFeedbackEmail($new_feedback);

        $redirect = $this->get('portal_validation')->getPasswordRedirectIfRequired($person, $request, $destination);
        if ($redirect) {
            return $redirect;
        }

        return $this->redirect($destination);
    }

    /**
     * @param mixed  $person
     * @param string $ip
     */
    public function submitNewFeedbackAbuseCheck($person, $ip)
    {
        $check = new SubmitFeedbackAbuseCheck($person, $ip);
        $this->getAntiAbuseService()->check($check);
    }

    /**
     * @Route("/feedback/browse/{filter_uri}", name="portal_feedback_browse", defaults={"query_path":""}, requirements={"filter_uri":".*"})
     * @Method("GET")
     * @Security("is_granted('USE_FEEDBACK')")
     * @PageHttpCache()
     */
    public function browseAction(Request $request, $filter_uri)
    {
        $page   = $request->query->get('page', 1);
        $person = $this->getUser() ?: new PersonGuest();

        try {
            $uri_helper = new FeedbackFilterUriHelper();
            $filter     = $uri_helper->extractFeedbackFilter($filter_uri);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('filter_uri could not be parsed');
        }

        // SECURITY
        // a permissions check, if the user can't see one of these filtered "types" (i.e. FeedbackCategory)
        $permissions_bag      = $this->getPermissionBag($person);
        $allowed_category_ids = $permissions_bag->getAllowedFeedbackCategoryIds();
        foreach ($filter->getTypes() as $type) {
            if (!in_array($type, $allowed_category_ids)) {
                throw new AccessDeniedException(
                    'you dont have access to a category you are trying to filter for
                ');
            }
        }

        // order was incorrect, redirect, but only if this is not an ajax request
        $generated_uri = $uri_helper->generateUriSegment($filter);
        if (!$request->isXmlHttpRequest() && $filter_uri != $generated_uri) {
            if (strlen($generated_uri) < 1) {
                // actually, in this case, it is all the defaults, so go back to the index
                return $this->redirectToRoute(
                    'portal_feedback',
                    ['page' => $page]
                );
            }

            return $this->redirectToRoute('portal_feedback_browse', [
                'filter_uri' => $generated_uri,
                'page'       => $page,
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedback();

        //
        // FILTER CATEGORIES
        //
        $feedback_types = $this->get('data.feedback')->getFeedbackCategoriesForPerson($person);
        $filter_js      = $this->generateFilterJs($filter, $feedback_types, $page);

        $page_options = [
            'page'              => $page,
            'feedback_types'    => $feedback_types,
            'count'             => $this->getBrandSetting('portal.per_page_content'),
            'show_pagination'   => true,
            'status'            => $filter->getStatus(),
            'status_categories' => $filter->getStatusCategories(),
            'types'             => $filter->getTypes(),
            'sort'              => $filter->getSort(),
            'sort_direction'    => $filter->getSortDirection(),
            'breadcrumbs'       => $breadcrumbs,
            'page_title'        => $this->createPageTitle()->feedback(),
            'filter_js'         => $filter_js,
            'rerendering_saved' => false, // wont happen here because we always rerender on index
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->renderThemeView(
                'Theme:Feedback:items_ajax_partial.html.twig',
                $page_options
            );
        }

        // setup and render an initial form that posts to /feedback
        $person       = $this->getUser() ?: new PersonGuest();
        $new_feedback = new Feedback();
        $new_feedback->setPerson($person);
        $form = $this->createForm('new_feedback', $new_feedback, [
            'person' => $person,
            'action' => $this->generateUrl('portal_feedback'),
        ]);

        $page_options = array_merge($page_options, [
            'form'               => $form->createView(),
            'form_was_submitted' => false,
            'user'               => $this->getUser(),
        ]);

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            $page_options
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

        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_FEEDBACK, $item)) {
            $form_handler = $this->get('form_handler.comment');
            $comment      = new FeedbackComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $new_comment_form = $form_handler->createForm($comment, $request);
            $form_result      = $form_handler->handle($new_comment_form, $request, $item, $comment);
            if ($form_result instanceof Response) {
                return $form_result;
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildFeedbackView($item);

        //
        // RATING
        //
        $rating         = $this->findContentRating($item, $visitor_id);
        $item->can_rate = $this->isGranted(ContentRatingsVoter::RATE_FEEDBACK, $item);

        //
        // NUM RATINGS
        //
        list($show_rating_counts, $rating_counts) = $this->determineRatingCounts($item);

        //
        // SUBSCRIPTION
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.feedback_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_FEEDBACK, $item)
        ) {
            // waiting on info on the kb subs
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($item, $this->getUser());
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:view.html.twig',
            [
                'item'               => $item,
                'is_subscribed'      => $is_subscribed,
                'content_id'         => $item->getId(),
                'content_type'       => Feedback::CONTENT_TYPE,
                'new_comment_form'   => $new_comment_form ? $new_comment_form->createView() : null,
                'page_title'         => $this->createPageTitle()->feedback($item),
                'breadcrumbs'        => $breadcrumbs,
                'rating'             => $rating,
                'show_rating_counts' => $show_rating_counts,
                'rating_counts'      => $rating_counts,
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
            return $this->createAccessDeniedException('Module not allowed');
        }
        if (!$this->isGranted('RATE_FEEDBACK', $item)) {
            if ($this->getUser()) {
                return $this->createAccessDeniedException('Rate not allowed');
            }
            if ($request->getContentType() == 'json') {
                return new JsonResponse(['error' => 'Try to login']);
            } else {
                $this->addFlash('notice', 'You should login to vote');

                return $this->redirectToRoute('portal_login', ['_destination' => $this->generateUrl('portal_feedback_view', ['slug' => $item->getSlug()])]);
            }
        }
        if (!$item->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this feedback item is hidden');
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
     */
    public function articleSubscriptionAction(Feedback $item)
    {
        if (!$item->isVisibleOnPortal()) {
            throw $this->createNotFoundException('this feedback item is hidden');
        }

        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($item, $person)) {
            $subscriptions_helper->unsubscribeFromContent($item, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.feedback_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToContent($item, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.feedback_subscribe'));
        }

        return $this->redirectToRoute('portal_feedback_view', ['slug' => $item->getSlug()]);
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
        $default_status_category_id = $this->getBrandSetting('portal.default_feedback_status_category_id');
        $default_status_category    = $this->getFeedbackDataService()->getFeedbackStatusCategory($default_status_category_id);

        return $default_status_category;
    }

    /**
     * @param $filter
     * @param $feedback_types
     *
     * @return string
     */
    public function generateFilterJs(FeedbackFilter $filter, array $feedback_types, $page)
    {
        $allowed_types_parsed = [];
        foreach ($feedback_types as $cat) {
            $allowed_types_parsed[$cat->getId()] = $this->objectPhrase($cat);
        }

        $status_categories        = [];
        $status_categories_entity = $this->getRepo('DeskPRO:FeedbackStatusCategory')->findBy(['status_type' => FeedbackFilter::$statuses]);
        foreach ($status_categories_entity as $status_category) {
            $status_type = $status_category->getStatusType();
            if (!array_key_exists($status_type, $status_categories)) {
                $status_categories[$status_type] = [];
            }

            $status_categories[$status_type][] = [
                'id'    => $status_category->getId(),
                'title' => $this->objectPhrase($status_category),
            ];
        }

        $the_array = [
            'filter'    => array_merge($filter->toArray(), ['page' => $page]),
            'available' => [
                'status'            => $this->transArray(FeedbackFilter::$statuses_translated),
                'status_categories' => $status_categories,
                'types'             => $allowed_types_parsed,
                'sorts'             => $this->transArray(FeedbackFilter::$sorts_translated),
                'sort_directions'   => $this->transArray(FeedbackFilter::$sort_directions_translated),
            ],
        ];

        $filter_js = json_encode(
            $the_array,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_NUMERIC_CHECK
        );

        return $filter_js;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function transArray(array $array)
    {
        $new_array = [];

        foreach ($array as $key => $phrase) {
            $new_array[$key] = $this->phrase($phrase);
        }

        return $new_array;
    }
}
