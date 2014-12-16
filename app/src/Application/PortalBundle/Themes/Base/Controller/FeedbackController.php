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
        return $this->render('Theme:Feedback:index.html.twig');
    }


    /**
     * @ParamConverter(name="feedback", converter="deskpro_slug")
     */
    public function viewAction(Request $request, Feedback $feedback)
    {
        return $this->render(
            'Theme:Feedback:view.html.twig', array(
                'feedback' => $feedback
            )
        );
    }

    /**
     * @Tag(name="feedback")
     */
    public function feedbackAction(TagRequest $request)
    {
        $allFeedback = $this->getFeedbackRepo()->createQueryBuilder('f');
        $pager = new Pagerfanta(new DoctrineORMAdapter($allFeedback));
        $pager->setMaxPerPage(3);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render(
            'Theme:Feedback:feedback.html.twig',
            array(
                'pager' => $pager,
                'feedbacks' => $pager->getCurrentPageResults()
            )
        );
    }

    /**
     * @Tag(name="feedback_items", default_options={"style":"items"})
     * @Tag(name="feedback_list_small", default_options={"style":"small"})
     *
     * @TagOptions({
     *      "defaults": {
     *          "style": "small",
     *          "count": 5
     *      },
     *      "allowedValues": {
     *          "style": {"items","small"}
     *      }
     * })
     */
    public function listAction(TagRequest $request, array $options)
    {
        $feedback  = $this->getFeedbackRepo()->getNewest(false, $options['count']);
        $total = $this->getFeedbackRepo()->countNotClosedNotHidden();

        return $this->render(
            sprintf('Theme:Feedback:list_%s.html.twig', $options['style']),
            array(
                'count_feedback' => $total,
                'feedback'    => $feedback
            )
        );
    }

    /**
     * @return \Application\AppBundle\DataService\FeedbackDataService
     */
    public function getFeedbackDataService()
    {
        return $this->get('data.feedback');
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\Feedback
     */
    protected function getFeedbackRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:Feedback');
    }
}
