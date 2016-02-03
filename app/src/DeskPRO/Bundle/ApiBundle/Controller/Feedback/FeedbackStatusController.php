<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FeedbackStatusController.
 *
 * @ApiModes("all")
 * @ApiTags("agent.feedback.feedback_status")
 */
class FeedbackStatusController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get list of feedback statuses",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback_statuses", name="api_feedback_statuses")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        /* @ToDo move below functionality into repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('f.id', 'f.status', 's.title', 'f.hidden_status')
            ->from('DeskPRO:Feedback', 'f')
            ->leftJoin('f.status_category', 's');
        $ids = $request->get('ids');
        if ($ids) {
            $qb
                ->andWhere('f.id IN (:ids)')
                ->setParameter('ids', explode(',', $ids));
        }

        $statuses = $qb->getQuery()->getResult();

        return View::create(
            $this->createRepresentation($statuses),
            Response::HTTP_OK
        );
    }
}
