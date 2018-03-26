<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class FeedbackController extends AbstractController
{
    /**
     * @Tag(name="feedback_list_simple", default_options={"style":"simple"}, esi=true)
     * @Tag(name="feedback_list_detail", default_options={"style":"detail", "show_pager":true}, allow_route_params=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "style": "detail",
     *          "count": 10,
     *          "page": 1,
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
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $person = $this->getUser() ?: new PersonGuest();

        $filter = new FeedbackFilter([
            'status'            => $options['status'],
            'status_categories' => $options['status_categories'],
            'types'             => $options['types'],
            'sort'              => $options['sort'],
            'sort_direction'    => $options['sort_direction'],
        ]);

        $pager = $this->getFeedbackDataService()->getItemsPager(
            (int) $options['page'],
            (int) $options['count'],
            $filter,
            $person
        );

        foreach ($pager as $item) {
            $item->can_rate = $this->isGranted(ContentRatingsVoter::RATE_FEEDBACK, $item);
        }

        $types   = $filter->getTypes();
        $allowed = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser())->getAllowedFeedbackCategoryIds();

        return $this->renderThemeView(
            sprintf('Theme:Feedback:FeedbackList/%s.html.twig', $options['style']),
            [
                'pager'              => $pager,
                'show_category_link' => $options['show_category_link'],
                'show_pager'         => $options['show_pager'],
                'filtered'           => count($allowed) - count(($types)) > 0,
            ]
        );
    }

    /**
     * @Tag(name="feedback_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "item": null
     *      },
     *      allowed_types={
     *          "item":{"Application\DeskPRO\Entity\Feedback","int","string"}
     *      },
     *      attribute_expressions={
     *          "item": "service('data.feedback').getItem(options['item'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, Feedback $item)
    {
        $comments = $this->getFeedbackDataService()->getItemComments($item, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
