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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Orb\Util\Strings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuidesController.
 *
 * @Feature("guides")
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
        $person = $this->getCurrentPerson();

        $guides = $this->getGuidesDataService()->getGuides($person);

        $guide = array_shift($guides);

        if (!$guide) {
            return $this->redirectToRoute('portal_home');
        }

        $topic = $guide->getTopics()->first();

        if (!$topic) {
            return $this->redirectToRoute('portal_home');
        }

        return $this->redirectToRoute('portal_guides_topic_permalink', ['slug' => $topic->getId(), 'guide_slug' => $guide->getSlug()]);
    }

    /**
     * @Route("/guides/{slug}", name="user_guides")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Guide $guide
     *
     * @return Response
     */
    public function browseAction(Guide $guide)
    {
        $topic = $guide->getTopics()->first();

        if (!$topic) {
            return $this->redirectToRoute('portal_home');
        }

        return $this->redirectToRoute('portal_guides_topic_permalink', ['slug' => $topic->getId(), 'guide_slug' => $guide->getSlug()]);
    }

    /**
     * @Route("/guides/{guide_slug}/{slug}", name="portal_guides_topic_view_short")
     * @Route("/guides/{guide_slug}{parents_slug}/{slug}", requirements={"parents_slug" = "(/.+)?"}, name="portal_guides_topic_view")
     * @Route("/guides/{guide_slug}/{slug}", name="portal_guides_topic_permalink")
     * @ParamConverter(name="topic", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_TOPIC', topic)")
     * @PageHttpCache(content="topic")
     *
     * @param Request $request
     * @param Topic   $topic
     * @param string  $guide_slug
     * @param string  $parents_slug
     *
     * @return Response
     */
    public function viewAction(Request $request, Topic $topic, $guide_slug, $parents_slug = '')
    {
        $topicParentsSlug = $topic->getParentsSlug();
        if ($parents_slug !== $topicParentsSlug || $guide_slug !== $topic->getGuideSlug()) {
            return $this->redirectToRoute(
                'portal_guides_topic_view',
                [
                    'slug'         => $topic->getSlug(),
                    'parents_slug' => $topicParentsSlug,
                    'guide_slug'   => $topic->getGuideSlug(),
                ]
            );
        }

        if (!$topic->getParent()) {
            /** @var Topic $childTopic */
            $childTopic = $topic->getChildren()->first();

            if ($childTopic) {
                return $this->redirectToRoute(
                    'portal_guides_topic_view',
                    [
                        'slug'         => $childTopic->getSlug(),
                        'parents_slug' => $childTopic->getParentsSlug(),
                        'guide_slug'   => $childTopic->getGuideSlug(),
                    ]
                );
            }
        }

        $serializer = $this->get('serializer');

        $person = $this->getCurrentPerson();

        $guides = $this->getGuidesDataService()->getGuides($person);

        return $this->renderThemeView(
            'Theme:Guides:view.html.twig',
            [
                'topic'       => $topic,
                'topic_json'  => Strings::escapeForJson($serializer->serialize($topic, 'json', new SideloadSerializationContext())),
                'guide'       => $topic->getGuide(),
                'guides_json' => Strings::escapeForJson($serializer->serialize($guides, 'json', new SideloadSerializationContext())),
            ]
        );
    }

    /**
     * @Route("/guide_doc")
     * @Security("is_granted('USE_GUIDES')")
     *
     * @return Response
     */
    public function markdownDocAction()
    {
        return $this->renderThemeView(
            'Theme:Guides:doc.html.twig'
        );
    }

    /**
     * @Route("/guide_pdf/{slug}", name="guides_pdf")
     * @ParamConverter(name="guide", converter="deskpro_slug")
     * @Security("is_granted('USE_GUIDES') and is_granted('VIEW_GUIDE', guide)")
     * @PageHttpCache()
     *
     * @param Guide $guide
     *
     * @return Response
     */
    public function guideFullAction(Guide $guide)
    {
        return $this->renderThemeView(
            'Theme:Guides:full.html.twig',
            [
                'guide' => $guide,
            ]
        );
    }
}
