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

namespace Application\PortalBundle\Controller;


use Application\DeskPRO\Entity\Feedback;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Zend\Feed\Writer\Extension\ITunes\Renderer\Feed;

class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback.{_format}", name="portal_feedback", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function indexAction(Request $request, $_format)
    {
        $page = $request->query->get('page', 1);
        $per_page = $request->query->get('per_page', 10); // TODO: brand setting?


        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getFeedbackDataService()->getItemsPager(
                $page,
                $per_page
            );

            return $this->render('PortalBundle:Feedback:feed.rss.twig', array(
                'pager' => $pager,
                'category' => null
            ));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:index.html.twig',
            array(
                'page' => $page,
                'count' => $per_page,
                'show_pagination' => true
            )
        );
    }


    /**
     * @Route("/feedback/view/{slug}", name="portal_feedback_view")
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK')")
     */
    public function viewAction(Request $request, Feedback $item)
    {
        $rating = $this->getRatingsHelper()->getPersonRating($item, $this->getUser());


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Feedback:view.html.twig',
            array(
                'item' => $item,
                'rating' => $rating
            )
        );
    }

    /**
     * @Route("/feedback/view/{slug}/vote-up", name="portal_feedback_vote_up", defaults={"up_or_down":"up"})
     * @Route("/feedback/view/{slug}/vote-down", name="portal_feedback_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="item", converter="deskpro_slug")
     * @Security("is_granted('USE_FEEDBACK') and is_granted('RATE_FEEDBACK', item)")
     */
    public function feedbackRateAction(Feedback $item, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($item, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($item, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_feedback_view', array('slug' => $item->getSlug()));
    }
}
