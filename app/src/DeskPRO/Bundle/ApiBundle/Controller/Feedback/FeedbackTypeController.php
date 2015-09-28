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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
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
     * @return View
     * @throws \LogicException
     */
    public function cgetAction()
    {
        $feedbackTypes = $this->getDoctrine()->getRepository('DeskPRO:FeedbackCategory')->findAll();

        return View::create(
            $this->dataSerialize($feedbackTypes),
            Response::HTTP_OK
        );
    }


}