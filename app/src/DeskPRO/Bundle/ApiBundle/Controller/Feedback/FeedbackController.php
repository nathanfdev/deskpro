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
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;

/**
 * API access to feedback.
 */
class FeedbackController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get count of feedback awaiting validation",
     *      parameters={
     *          {
     *              "name"="awaiting_validation",
     *              "requirement"="\d+",
     *              "description"="count of feedback awaiting validation",
     *              "dataType"="integer",
     *              "required"=true
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/feedback/counts", name="api_feedback_count")
     * @return View
     * @throws \LogicException
     */
    public function getCountAwaitingValidationAction()
    {
        $count = $this->get('data.feedback')->countAwaitingValidation();

        return View::create(
            $this->dataSerialize(new PrimitiveArray([$count])),
            Response::HTTP_OK
        );
    }
}
