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
namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\FeedbackStatus;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FeedbackStatusController.
 *
 * @ApiModes("all")
 */
class FeedbackStatusController extends BaseController
{
    /**
     * Fetch feedback statuses.
     *
     * @ApiDoc(
     *     section="Feedback",
     *     tags={"unstable"="#ff6666", "feedback"="#4422bb"},
     *     resourceDescription="Operations about feedback",
     *     description="get list of feedback statuses",
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\FeedbackStatus"
     * )
     * @Annotations\Get("/feedback_statuses", name="api_feedback_statuses")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $statuses = $this->getRepository('Application\\DeskPRO\\Entity\\Feedback')->findAll();

        return View::create(
            $this->wrap($statuses, FeedbackStatus::class),
            Response::HTTP_OK
        );
    }
}
