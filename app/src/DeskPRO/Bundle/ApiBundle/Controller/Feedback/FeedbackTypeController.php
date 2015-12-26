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
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to feedback types.
 */
class FeedbackTypeController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a filtered list of feedback types",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback_types", name="api_feedback_types")
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function cgetAction()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('type.id', 'type.title', 'COUNT(feedback.id) as counter')
            ->from('DeskPRO:FeedbackCategory', 'type')
            ->leftJoin(
                'DeskPRO:Feedback',
                'feedback',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'feedback.category = type.id'
            )
            ->groupBy('type.id');

        $types = $qb->getQuery()->getArrayResult();

        return View::create(
            $this->createRepresentation($types),
            Response::HTTP_OK
        );
    }
}
