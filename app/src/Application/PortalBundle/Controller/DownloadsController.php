<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Controller;


use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DownloadsController extends AbstractController
{
    /**
     * @Route("/downloads.{_format}", name="portal_downloads", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function indexAction(Request $request, $_format)
    {
        $page = $request->query->get('page', 1);
        $per_page = $request->query->get('per_page', 10); // TODO: brand setting?


        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getDownloadsDataService()->getDownloadsPager(
                null,
                $page,
                $per_page
            );

            return $this->render('PortalBundle:Downloads:feed.rss.twig', array(
                'pager' => $pager,
                'category' => null
            ));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView('Theme:Downloads:index.html.twig');
    }

    /**
     * @Route("/downloads/{slug}.{_format}", name="portal_downloads_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function browseAction(Request $request, DownloadCategory $category, $_format)
    {
        $page = $request->query->get('page', 1);
        $per_page = $request->query->get('per_page', 10); // TODO: brand setting?


        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getDownloadsDataService()->getDownloadsPager(
                $category,
                $page,
                $per_page
            );

            return $this->render('PortalBundle:Downloads:feed.rss.twig', array(
                'pager' => $pager,
                'category' => $category
            ));
        }


        //
        // SUBSCRIPTIONS
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Downloads:browse.html.twig',
            array(
                'category' => $category,
                'is_subscribed' => $is_subscribed,
                'page' => $page,
                'count' => $per_page,
                'show_pagination' => true
            )
        );
    }


    /**
     * @Route("/downloads/files/{slug}", name="portal_downloads_view")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function viewAction(Request $request, Download $file)
    {
        //
        // RELATED, RATING
        //
        $related = $this->getDownloadsDataService()->getRelatedFiles($file);
        $rating = $this->getRatingsHelper()->getPersonRating($file, $this->getUser());


        //
        // SUBSCRIPTIONS
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.downloads_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($file, $this->getUser());
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Downloads:view.html.twig',
            array(
                'download' => $file,
                'is_subscribed' => $is_subscribed,
                'related_files' => $related,
                'rating' => $rating
            )
        );
    }


    /**
     * @Route("/downloads/files/{slug}/download", name="portal_downloads_download")
     * @ParamConverter(name="download", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS')")
     */
    public function downloadAction(Request $request, Download $file)
    {
        return new Response('downlading file...');
    }

    /**
     * @Route("/downloads/files/{slug}/vote-up", name="portal_downloads_vote_up", defaults={"up_or_down":"up"})
     * @Route("/downloads/files/{slug}/vote-down", name="portal_downloads_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('RATE_DOWNLOADS', file)")
     */
    public function downloadRateAction(Download $file, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($file, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($file, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_downloads_view', array('slug' => $file->getSlug()));
    }

    /**
     * @Route("/downloads/files/{slug}/toggle-subscription", name="portal_downloads_files_toggle_subscription")
     * @ParamConverter(name="file", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('SUBSCRIBE_DOWNLOADS', file)")
     */
    public function downloadsSubscriptionAction(Download $file)
    {
        $person = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($file, $person)) {
            $subscriptions_helper->unsubscribeFromContent($file, $person);
            $this->addFlash('success', 'Successfully unsubscribed from this download.');
        } else {
            $subscriptions_helper->subscribeToContent($file, $person);
            $this->addFlash('success', 'You have successfully subscribed to this download. You will be notified when it is updated.');
        }

        return $this->redirectToRoute('portal_downloads_view', array('slug' => $file->getSlug()));
    }


    /**
     * @Route("/downloads/category/toggle-subscription/{slug}", name="portal_downloads_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_DOWNLOADS') and is_granted('SUBSCRIBE_DOWNLOADS_CATEGORIES', category)")
     */
    public function downloadsCategorySubscriptionAction(DownloadCategory $category)
    {
        $person = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedCategory($category, $person)) {
            $subscriptions_helper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', 'Successfully unsubscribed from this category.');
        } else {
            $subscriptions_helper->subscribeToCategory($category, $person);
            $this->addFlash('success', 'You have successfully subscribed to this category. You will be notified when it is updated.');
        }

        return $this->redirectToRoute('portal_downloads_browse', array('slug' => $category->getSlug()));
    }

    /**
     * @Route("/downloads/files/subscriptions/unsubscribe", name="portal_downloads_unsubscribe_all")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_DOWNLOADS')")
     */
    public function downloadsUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('downloads', $this->getUser());

        $this->addFlash('success', 'Unsubscribed from all Downloads subscriptions');

        return $this->redirectToRoute('portal_index');
    }

    /**
     * @return \Application\AppBundle\DataService\DownloadsDataService
     */
    public function getDownloadsDataService()
    {
        return $this->get('data.downloads');
    }
}
