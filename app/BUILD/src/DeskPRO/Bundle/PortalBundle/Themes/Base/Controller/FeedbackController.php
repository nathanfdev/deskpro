<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

        $filter = new FeedbackFilter(array(
            'status'            => $options['status'],
            'status_categories' => $options['status_categories'],
            'types'             => $options['types'],
            'sort'              => $options['sort'],
            'sort_direction'    => $options['sort_direction'],
        ));

        $pager = $this->getFeedbackDataService()->getItemsPager(
            $options['page'],
            $options['count'],
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
            array(
                'pager'              => $pager,
                'show_category_link' => $options['show_category_link'],
                'show_pager'         => $options['show_pager'],
                'filtered'           => count($allowed) - count(($types)) > 0,
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

        return $this->renderThemeView('Theme:Common:comments.html.twig', array(
            'comments' => $comments,
        ));
    }
}
