<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CommunityController extends AbstractController
{
    /**
     * @Tag(name="community_list_simple", default_options={"style":"simple"}, esi=true)
     * @Tag(name="community_list_detail", default_options={"style":"detail", "show_pager":true}, allow_route_params=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "style": "detail",
     *          "count": 10,
     *          "page": 1,
     *          "q": "",
     *          "status": "all",
     *          "status_categories": {},
     *          "types": {},
     *          "sort": "date",
     *          "sort_direction": "desc",
     *          "show_category_link": true,
     *          "show_pager": false
     *      },
     *      allowed_values={
     *          "style": {"simple", "detail"},
     *          "sort": {"date", "most-popular", "highest-rating", "most-discussed", "most-views"},
     *          "sort_direction": {"desc", "asc"},
     *          "status": {"all","active","closed"}
     *      }
     * )
     *
     * @Security("is_granted('USE_COMMUNITY')")
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $context = array_merge($this->getCommunityDataService()->getFilteredTopicList($options, $this->getUser()), [
            'show_category_link' => $options['show_category_link'],
            'show_pager'         => $options['show_pager'],
        ]);

        return $this->renderThemeView(
            sprintf('Theme:Community:CommunityTopicsList/%s.html.twig', $options['style']),
            $context
        );
    }

    /**
     * @Tag(name="community_topic_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "topic": null
     *      },
     *      allowed_types={
     *          "topic":{"Application\DeskPRO\Entity\CommunityTopic","int","string"}
     *      },
     *      attribute_expressions={
     *          "topic": "service('data.community').getItem(options['topic'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_COMMUNITY')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, CommunityTopic $topic)
    {
        $comments = $this->getCommunityDataService()->getItemComments($topic, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
