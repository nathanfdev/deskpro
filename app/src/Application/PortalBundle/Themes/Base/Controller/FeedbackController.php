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
use Application\PortalBundle\Request\TagRequest;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends AbstractController
{
    public function indexAction(Request $request)
    {
        return $this->render('Theme:Feedback:index.html.twig', array(
            'page' => $request->query->get('page', 1),
            'count' => 2,
            'show_pagination' => true
        ));
    }


    /**
     * @ParamConverter(name="item", converter="deskpro_slug")
     */
    public function viewAction(Request $request, Feedback $item)
    {
        return $this->render(
            'Theme:Feedback:view.html.twig', array(
                'item' => $item
            )
        );
    }

    /**
     * @Tag(name="feedback")
     *
     * @TagOptions(
     *      defaults={
     *          "count": 2,
     *          "page": 1,
     *          "show_pagination": true
     *      }
     * )
     */
    public function feedbackAction(TagRequest $tag_request, array $options)
    {
        return $this->render(
            'Theme:Feedback:Tag/feedback.html.twig',
            array(
                'count' => $options['count'],
                'page' => $options['page'],
                'show_pagination' => $options['show_pagination']
            )
        );
    }

    /**
     * @Tag(name="feedback_items")
     * @Tag(name="feedback_items_list", default_options={"style":"list"})
     * @Tag(name="feedback_items_row", default_options={"style":"row"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "row",
     *          "count": 2,
     *          "page": 1
     *      },
     *      allowed_values={
     *          "style": {"list","row"}
     *      }
     * )
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $pager = $this->getFeedbackDataService()->getItemsPager($options['page'], $options['count']);

        return $this->render(
            sprintf('Theme:Feedback:Tag/items_%s.html.twig', $options['style']),
            array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="feedback_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "show_pagination": true,
     *          "count": 2,
     *          "page": 1
     *      },
     * )
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $pager = $this->getFeedbackDataService()->getItemsPager($options['page'], $options['count']);

        return $this->render('Theme:Common:pager.html.twig', array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="feedback_breadcrumbs")
     *
     * @TagOptions(
     *      defaults={"item": null},
     *      allowed_types={"item": {"Application\DeskPRO\Entity\Feedback", "int", "null"}}
     * )
     */
    public function breadcrumbsAction(TagRequest $request, array $options)
    {
        $item = $this->getFeedbackDataService()->getItem($options['item']);

        return $this->render('Theme:Feedback:Tag/breadcrumbs.html.twig', array(
            'item' => $item
        ));
    }

    /**
     * @return \Application\AppBundle\DataService\FeedbackDataService
     */
    public function getFeedbackDataService()
    {
        return $this->get('data.feedback');
    }
}
