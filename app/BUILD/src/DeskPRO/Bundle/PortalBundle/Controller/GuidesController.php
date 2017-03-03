<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuidesController.
 */
class GuidesController extends AbstractController
{
    /**
     * @Route("/guides.{_format}", name="portal_guides", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/guides", name="user_guides_home")
     * @Security("is_granted('USE_GUIDES')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param string  $_format
     *
     * @return Response
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        $guides = $this->getGuidesDataService()->getGuides($person);

        if (count($guides) === 1) {
            return $this->redirectToRoute('portal_guides_browse', ['slug' => $guides[0]->getSlug()]);
        }

        // RSS

//        if ('rss' === $_format) {
//            $pager = $this->getGuidesDataService()->getGuidesPager(
//                null,
//                $request->query->getInt('page', 1),
//                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
//                $person
//            );

//            return $this->render('PortalBundle:Guides:feed.rss.twig', [
//                'pager'      => $pager,
//                'category'   => null,
//                'page_title' => $this->createPageTitle()->guides(),
//            ]);
//        }
//        $rssLink = $this->generateUrl(
//            'portal_guides',
//            ['_format' => 'rss']
//        );

        // BREADCRUMBS

//        $breadcrumbs = $this->getBreadcrumbGenerator()->buildGuides();

        // SUBSCRIPTION

//        $isSubscribed = false;
//        if ($this->getUser() && $this->getBrandSetting('user.guides_subscriptions', false)) {
//            // waiting info regarding article category subscriptions
//            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('guides', $this->getUser());
//        }

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Guides:index.html.twig',
            [
                'page'               => $page,
                'count'              => $this->getBrandSetting('portal.per_page_content'),
                'breadcrumbs'        => $breadcrumbs,
                'show_category_link' => true,
                'page_title'         => $this->createPageTitle()->guides(),
                'rss_link'           => $rssLink,
                'is_subscribed'      => $isSubscribed,
            ]
        );
    }

    /**
     * @Route("/guides/{slug}.{_format}", name="portal_guides_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/guides/{slug}", name="user_guides")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param Guide   $guide
     * @param string  $_format
     *
     * @return Response
     */
    public function browseAction(Request $request, Guide $guide, $_format)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getCurrentPerson();

        // RSS

//        if ('rss' === $_format) {
//            $pager = $this->getGuidesDataService()->getGuidesPager(
//                $guide,
//                $page,
//                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
//                $person
//            );
//
//            return $this->render('PortalBundle:Guides:feed.rss.twig', [
//                'pager'      => $pager,
//                'category'   => $guide,
//                'page_title' => $this->createPageTitle()->guides($guide),
//            ]);
//        }
//        $rssLink = $this->generateUrl('portal_guides_browse', ['slug' => $guide->getSlug(), '_format' => 'rss']);

        // BREADCRUMBS

//        if ($guide) {
//            $breadcrumbs = $this->getBreadcrumbGenerator()->buildGuidesCategory($guide);
//        } else {
//            $breadcrumbs = $this->getBreadcrumbGenerator()->buildGuides();
//        }

        // SUBSCRIBE

//        $isSubscribed = false;
//        if (
//            $this->getBrandSetting('user.guides_subscriptions', false)
//            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOAD_CATEGORY, $guide)
//        ) {
//            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($guide, $this->getUser());
//        }

        // PAGER

//        $count = $this->getBrandSetting('portal.per_page_content');
        $topics = $this->getGuidesDataService()->getGuideChildren($guide, $person);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Guides:browse.html.twig',
            [
                'guide'         => $guide,
//                'breadcrumbs'   => $breadcrumbs,
//                'count'         => $count,
                'page'          => $page,
                'topics'         => $topics,
//                'is_subscribed' => $isSubscribed,
                'page_title'    => $this->createPageTitle()->guides($guide),
//                'rss_link'      => $rssLink,
            ]
        );
    }

    /**
     * @Route("/guides/files/{slug}", name="portal_guides_view")
     * @Route("/guides/files/{slug}", name="user_guides_file")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_DOWNLOAD', file)")
     * @PageHttpCache(content="file")
     *
     * @param Request $request
     * @param Topic   $file
     * @param string  $visitor_id
     *
     * @return Response
     */
    public function viewAction(Request $request, Topic $file, $visitor_id)
    {
        if (!$file->getBlob() && !$file->getFileurl()) {
            throw $this->createNotFoundException('could not find downloadable content for download id='.$file->getId());
        }

        // COMMENT FORM

        $newCommentForm = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_DOWNLOAD, $file)) {
            $formHandler = $this->get('form_handler.comment');
            $comment     = new DownloadComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $form_result    = $formHandler->handle($newCommentForm, $request, $file, $comment);
            if ($form_result instanceof Response) {
                return $form_result;
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildGuidesFile($file);

        // RATING

        $rating = $this->findContentRating($file, $visitor_id);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($file);

        // SUBSCRIPTION

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.guides_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOAD, $file)
        ) {
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedContent($file, $this->getUser());
        }

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Guides:view.html.twig',
            [
                'file'               => $file,
                'content_type'       => Topic::CONTENT_TYPE,
                'content_id'         => $file->getId(),
                'new_comment_form'   => $newCommentForm ? $newCommentForm->createView() : null,
                'breadcrumbs'        => $breadcrumbs,
                'rating'             => $rating,
                'is_subscribed'      => $isSubscribed,
                'page_title'         => $this->createPageTitle()->guides($file),
                'show_rating_counts' => $showRatingCounts,
                'rating_counts'      => $ratingCounts,
                'lockout'            => $check->isLockoutRecommended(),
                'lockout_time'       => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * @Route("/guides/files/{slug}/download", name="portal_guides_download")
     * @ParamConverter("file", options={"slug" = "slug"})
     * @Security("is_granted('USE_GUIDES') and is_granted('DOWNLOAD_DOWNLOAD', file)")
     *
     * @param Topic $file
     *
     * @return Response
     */
    public function downloadAction(Topic $file)
    {
        $file->incrementDownloadCount();
        $this->getEm()->flush($file);

        if ($file->getFileurl()) {
            return $this->redirect($file->getFileurl());
        }

        return $this->redirectToRoute('serve_blob', [
            'blob_auth_id' => $file->getBlob()->getAuthId(),
            'filename'     => $file->getFilenameSafe(),
            'dl'           => 1,
        ]);
    }

    /**
     * @Route("/guides/files/{slug}/vote-up", name="portal_guides_vote_up", defaults={"up_or_down":"up"})
     * @Route("/guides/files/{slug}/vote-down", name="portal_guides_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('RATE_DOWNLOAD', file)")
     * @AutoPostOnGetRequest()
     *
     * @param Topic  $file
     * @param string $visitor_id
     * @param string $up_or_down
     *
     * @return Response
     */
    public function downloadRateAction(Topic $file, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($file, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($file, $visitor_id, $person);
        }

        $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

        return $this->redirectToRoute('portal_guides_view', ['slug' => $file->getSlug()]);
    }

    /**
     * @Route("/guides/files/{slug}/toggle-subscription", name="portal_guides_files_toggle_subscription")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('SUBSCRIBE_DOWNLOAD', file)")
     * @AutoPostOnGetRequest()
     *
     * @param Topic $file
     *
     * @return Response
     */
    public function guidesSubscriptionAction(Topic $file)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedContent($file, $person)) {
            $subscriptionsHelper->unsubscribeFromContent($file, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToContent($file, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_subscribe'));
        }

        return $this->redirectToRoute('portal_guides_view', ['slug' => $file->getSlug()]);
    }

    /**
     * @Route("/guides/category/toggle-subscription/{slug}", name="portal_guides_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('SUBSCRIBE_DOWNLOAD_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     *
     * @param Guide $guide
     *
     * @return Response
     */
    public function guidesCategorySubscriptionAction(Guide $guide)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedCategory($guide, $person)) {
            $subscriptionsHelper->unsubscribeFromCategory($guide, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.guide_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToCategory($guide, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.guide_subscribe'));
        }

        return $this->redirectToRoute('portal_guides_browse', ['slug' => $guide->getSlug()]);
    }

    /**
     * @Route("/guides/root/toggle-subscription", name="portal_guides_root_category_toggle_subscription")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_GUIDES')")
     * @AutoPostOnGetRequest()
     *
     * @return Response
     */
    public function guidesRootCategorySubscriptionAction()
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedRootCategory('guides', $person)) {
            $subscriptionsHelper->unsubscribeFromRootCategory('guides', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('guides', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_guides');
    }

    /**
     * @Route("/guides/files/subscriptions/unsubscribe", name="portal_guides_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_GUIDES')")
     * @AutoPostOnGetRequest()
     */
    public function guidesUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('guides', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.download_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }
}
