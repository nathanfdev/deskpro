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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
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
                'guide' => $guide,
//                'breadcrumbs'   => $breadcrumbs,
//                'count'         => $count,
                'page'   => $page,
                'topics' => $topics,
//                'is_subscribed' => $isSubscribed,
                'page_title' => $this->createPageTitle()->guides($guide),
//                'rss_link'      => $rssLink,
            ]
        );
    }

    /**
     * @Route("/guides/{guide_slug}/{slug}")
     * @Route("/guides/topic/{slug}", name="portal_topic_view")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_TOPIC', topic)")
     * @PageHttpCache(content="topic")
     *
     * @param Request $request
     * @param Topic   $topic
     * @param int     $visitor_id
     */
    public function viewAction(Request $request, Topic $topic, $visitor_id)
    {
    }
}
