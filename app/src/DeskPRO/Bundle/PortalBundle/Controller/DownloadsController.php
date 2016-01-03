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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\DownloadComment;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadsController extends AbstractController
{
    /**
     * @Route("/downloads.{_format}", name="portal_downloads", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/downloads", name="user_downloads_home")
     * @Security("is_granted('USE_DOWNLOADS')")
     * @PageHttpCache()
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getDownloadsDataService()->getDownloadsPager(
                null,
                $request->query->get('page', 1),
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:Downloads:feed.rss.twig', array(
                'pager'      => $pager,
                'category'   => null,
                'page_title' => $this->createPageTitle()->downloads(),
            ));
        }
        $rss_link = $this->generateUrl(
            'portal_downloads',
            array('_format' => 'rss')
        );

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDownloads();

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Downloads:index.html.twig',
            array(
                'page'               => $page,
                'count'              => $this->getBrandSetting('portal.per_page_content'),
                'breadcrumbs'        => $breadcrumbs,
                'show_category_link' => true,
                'page_title'         => $this->createPageTitle()->downloads(),
                'rss_link'           => $rss_link,
            )
        );
    }

    /**
     * @Route("/downloads/{slug}.{_format}", name="portal_downloads_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/downloads/{slug}", name="user_downloads")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('VIEW_DOWNLOAD_CATEGORY', category)")
     * @PageHttpCache()
     */
    public function browseAction(Request $request, DownloadCategory $category, $_format)
    {
        $page   = $request->query->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getDownloadsDataService()->getDownloadsPager(
                $category,
                $page,
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:Downloads:feed.rss.twig', array(
                'pager'      => $pager,
                'category'   => $category,
                'page_title' => $this->createPageTitle()->downloads($category),
            ));
        }
        $rss_link = $this->generateUrl('portal_downloads_browse', array('slug' => $category->getSlug(), '_format' => 'rss'));

        //
        // BREADCRUMBS
        //
        if ($category) {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildDownloadsCategory($category);
        } else {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildDownloads();
        }

        //
        // SUBSCRIBE
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOAD_CATEGORY, $category)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        //
        // PAGER
        //
        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getDownloadsDataService()->getDownloadsPager($category, $page, $count, $person);

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Downloads:browse.html.twig',
            array(
                'category'      => $category,
                'breadcrumbs'   => $breadcrumbs,
                'count'         => $count,
                'page'          => $page,
                'pager'         => $pager,
                'is_subscribed' => $is_subscribed,
                'page_title'    => $this->createPageTitle()->downloads($category),
                'rss_link'      => $rss_link,
            )
        );
    }

    /**
     * @Route("/downloads/files/{slug}", name="portal_downloads_view")
     * @Route("/downloads/files/{slug}", name="user_downloads_file")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('VIEW_DOWNLOAD', file)")
     * @PageHttpCache(content="file")
     */
    public function viewAction(Request $request, Download $file, $visitor_id)
    {
        // TODO: is there ever an instance that there would NOT be a blob associated with a download entity??
        if (!$file->getBlob()) {
            throw $this->createNotFoundException('could not find downloadable content for download id='.$file->getId());
        }

        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_DOWNLOAD, $file)) {
            $form_handler = $this->get('form_handler.comment');
            $comment      = new DownloadComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $new_comment_form = $form_handler->createForm($comment, $request);
            $form_result      = $form_handler->handle($new_comment_form, $request, $file, $comment);
            if ($form_result instanceof Response) {
                return $form_result;
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildDownloadsFile($file);

        //
        // RATING
        //
        $rating = $this->findContentRating($file, $visitor_id);

        //
        // NUM RATINGS
        //
        list($show_rating_counts, $rating_counts) = $this->determineRatingCounts($file);

        //
        // SUBSCRIPTION
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOAD, $file)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($file, $this->getUser());
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Downloads:view.html.twig',
            array(
                'file'               => $file,
                'content_type'       => Download::CONTENT_TYPE,
                'content_id'         => $file->getId(),
                'new_comment_form'   => $new_comment_form ? $new_comment_form->createView() : null,
                'breadcrumbs'        => $breadcrumbs,
                'rating'             => $rating,
                'is_subscribed'      => $is_subscribed,
                'page_title'         => $this->createPageTitle()->downloads($file),
                'show_rating_counts' => $show_rating_counts,
                'rating_counts'      => $rating_counts,
            )
        );
    }

    /**
     * @Route("/downloads/files/{slug}/download", name="portal_downloads_download")
     * @ParamConverter("file", options={"slug" = "slug"})
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('DOWNLOAD_DOWNLOAD', file)")
     */
    public function downloadAction(Request $request, Download $file)
    {
        $file->incrementDownloadCount();
        $this->getEm()->flush($file);

        if ($file->getFileurl()) {
            return $this->redirect($file->getFileurl());
        }

        return $this->redirectToRoute('serve_blob', array(
            'blob_auth_id' => $file->getBlob()->getAuthId(),
            'filename'     => $file->getFilenameSafe(),
            'dl'           => 1,
        ));
    }

    /**
     * @Route("/downloads/files/{slug}/vote-up", name="portal_downloads_vote_up", defaults={"up_or_down":"up"})
     * @Route("/downloads/files/{slug}/vote-down", name="portal_downloads_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('RATE_DOWNLOAD', file)")
     * @AutoPostOnGetRequest()
     */
    public function downloadRateAction(Download $file, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($file, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($file, $visitor_id, $person);
        }

        $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

        return $this->redirectToRoute('portal_downloads_view', array('slug' => $file->getSlug()));
    }

    /**
     * @Route("/downloads/files/{slug}/toggle-subscription", name="portal_downloads_files_toggle_subscription")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('SUBSCRIBE_DOWNLOAD', file)")
     * @AutoPostOnGetRequest()
     */
    public function downloadsSubscriptionAction(Download $file)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($file, $person)) {
            $subscriptions_helper->unsubscribeFromContent($file, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToContent($file, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_subscribe'));
        }

        return $this->redirectToRoute('portal_downloads_view', array('slug' => $file->getSlug()));
    }

    /**
     * @Route("/downloads/category/toggle-subscription/{slug}", name="portal_downloads_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('SUBSCRIBE_DOWNLOAD_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     */
    public function downloadsCategorySubscriptionAction(DownloadCategory $category)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedCategory($category, $person)) {
            $subscriptions_helper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_cat_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.download_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_downloads_browse', array('slug' => $category->getSlug()));
    }

    /**
     * @Route("/downloads/files/subscriptions/unsubscribe", name="portal_downloads_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_DOWNLOADS')")
     * @AutoPostOnGetRequest()
     */
    public function downloadsUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('downloads', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.download_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }
}
