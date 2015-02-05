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

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\DeskPRO\Entity\Feedback;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Model\FeedbackFilter;
use Application\PortalBundle\Request\TagRequest;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class FeedbackController extends AbstractController
{
    /**
     * @Tag(name="feedback_items")
     * @Tag(name="feedback_items_list", default_options={"style":"list"})
     * @Tag(name="feedback_items_row", default_options={"style":"row"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "row",
     *          "count": 10,
     *          "page": 1,
     *          "status": "all",
     *          "status_categories": {},
     *          "types": {},
     *          "sort": "date",
     *          "sort_direction": "desc"
     *      },
     *      allowed_values={
     *          "style": {"list", "row"},
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
        $filter = new FeedbackFilter(array(
            'status' => $options['status'],
            'status_categories' => $options['status_categories'],
            'types' => $options['types'],
            'sort' => $options['sort'],
            'sort_direction' => $options['sort_direction']
        ));

        $pager = $this->getFeedbackDataService()->getItemsPager(
            $options['page'],
            $options['count'],
            $filter
        );

        return $this->renderThemeView(
            sprintf('Theme:Feedback:Tag/items_%s.html.twig', $options['style']),
            array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="item", esi=true)
     * @Cache(smaxage="10 minutes")
     *
     * @TagOptions(
     *      required={"item"},
     *      allowed_types={
     *          "item": {"Application\DeskPRO\Entity\Feedback", "int", "string", "null"}
     *      }
     * )
     */
    public function itemAction(TagRequest $tag_request, array $options)
    {
        $item = $this->getFeedbackDataService()->getItem($options['item']);

        return $this->renderThemeView(
            'Theme:Feedback:Tag/item.html.twig',
            array(
                'item' => $item
            )
        );
    }

    /**
     * @Tag(name="feedback_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "show_pagination": true,
     *          "count": 10,
     *          "page": 1,
     *          "status": "all",
     *          "status_categories": {},
     *          "types": {},
     *          "sort": "date",
     *          "sort_direction": "desc"
     *      },
     *      allowed_values={
     *          "sort": {"date", "most-popular", "highest-rating", "most-discussed", "most-views"},
     *          "sort_direction": {"desc", "asc"},
     *          "status": {"all","active","closed"}
     *      }
     * )
     *
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $filter = new FeedbackFilter(array(
            'status' => $options['status'],
            'status_categories' => $options['status_categories'],
            'types' => $options['types'],
            'sort' => $options['sort'],
            'sort_direction' => $options['sort_direction']
        ));

        $pager = $this->getFeedbackDataService()->getItemsPager(
            $options['page'],
            $options['count'],
            $filter
        );

        return $this->renderThemeView(
            'Theme:Common:pager.html.twig',
            array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="feedback_breadcrumbs", esi=true)
     * @Cache(smaxage="10 minutes")
     *
     * @TagOptions(
     *      defaults={"item": null},
     *      allowed_types={
     *          "item": {"Application\DeskPRO\Entity\Feedback", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function breadcrumbsAction(TagRequest $tag_request, array $options)
    {
        $item = $this->getFeedbackDataService()->getItem($options['item']);

        return $this->renderThemeView(
            'Theme:Feedback:Tag/breadcrumbs.html.twig',
            array(
                'item' => $item
            )
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
     *          "item":{"Application\DeskPRO\Entity\Feedback","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function commentsAction(TagRequest $tag_request, array $options)
    {
        $item = $this->getFeedbackDataService()->getItem($options['item']);
        $comments = $this->getFeedbackDataService()->getItemComments($item, $this->getUser());

        return $this->renderThemeView('Theme:Feedback:Tag/comments.html.twig', array(
            'item' => $item,
            'comments' => $comments
        ));
    }

    /**
     * @Tag(name="feedback_ratings", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"item": null},
     *      allowed_types={
     *          "item": {"Application\DeskPRO\Entity\Feedback", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function ratingsAction(TagRequest $tag_request, array $options)
    {
        $item = $this->getFeedbackDataService()->getItem($options['item']);
        $rating = $this->getRatingsHelper()->getPersonRating($item, $this->getUser());

        return $this->renderThemeView(
            'Theme:Feedback:Tag/ratings.html.twig',
            array(
                'rating' => $rating,
                'item' => $item
            )
        );
    }
}
