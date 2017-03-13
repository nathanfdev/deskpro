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

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GuidesController.
 *
 * @Feature("guides")
 */
class GuidesController extends AbstractController
{
    /**
     * @Tag(name="topic_list", allow_route_params=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *     defaults={
     *          "guide": null,
     *          "topic": null,
     *     },
     *     allowed_types={
     *          "guide":{"Application\DeskPRO\Entity\Guide","int","string"},
     *          "topic":{"Application\DeskPRO\Entity\Topic","int","string","null"}
     *     },
     *     attribute_expressions={
     *          "guide": "service('data.guides').getGuide(options['guide'])",
     *          "topic": "service('data.guides').getTopic(options['topic'])"
     *     }
     * )
     *
     * @Security("is_granted('USE_GUIDES')")
     *
     * @param TagRequest $tagRequest
     * @param array      $options
     * @param Guide      $guide
     * @param Topic      $topic
     *
     * @return Response
     */
    public function topicListAction(TagRequest $tagRequest, array $options, Guide $guide, Topic $topic = null)
    {
        $person = $this->getCurrentPerson();

        $topics = $this->getGuidesDataService()->getGuideChildren(
            $guide,
            $person
        );

        return $this->renderThemeView(
            'Theme:Guides:TopicList/list.html.twig',
            [
                'topics'        => $topics,
                'guide'         => $guide,
                'current_topic' => $topic,
            ]
        );
    }
}
