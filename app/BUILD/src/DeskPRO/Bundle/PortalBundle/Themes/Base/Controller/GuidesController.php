<?php

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
                'guide'         => $guide,
                'topics'        => $topics,
                'current_topic' => $topic,
            ]
        );
    }

    /**
     * @Tag(name="topic_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "topic": null
     *      },
     *      allowed_types={
     *          "topic":{"Application\DeskPRO\Entity\Topic","int","string"}
     *      },
     *      attribute_expressions={
     *          "topic": "service('data.guides').getTopic(options['topic'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_GUIDES')")
     *
     * @param TagRequest $tagRequest
     * @param array      $options
     * @param Topic      $topic
     *
     * @return Response
     */
    public function commentsAction(TagRequest $tagRequest, array $options, Topic $topic)
    {
        $comments = $this->getGuidesDataService()->getTopicComments($topic, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
