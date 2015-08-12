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

use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCountCriteria;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;

/**
 * API access to feedback.
 */
class FeedbackController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get feedback counts",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Get("/feedback/counts", name="api_feedback_count")
     * @param Request $request
     * @return View
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws BadRequestHttpException
     */
    public function getCountsAction(Request $request)
    {
        $dataService = $this->get('data.feedback');
        $params = $request->query->all();
        try {
            $criteria = FeedbackCountCriteria::fromParameters($params, new OptionsResolver(), $this->getUser());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $count = $dataService->countFeedback($criteria);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

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
     *          },
     *         {
     *              "name"="group_by",
     *              "requirement"="\w+",
     *              "description"="counts of feedback grouped by feedback category",
     *              "dataType"="string",
     *              "required"=true
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/feedback/counts", name="api_feedback_count")
     * @param Request $request
     * @return View
     * @throws \InvalidArgumentException
     */
    public function getCountAwaitingValidationAction(Request $request)
    {
        $count = [];
        $service = $this->get('data.feedback');
        if ($request->query->get('awaiting_validation')) {
            $count = $service->countAwaitingValidation();
        } elseif ($request->query->get('group_by') === 'category') {
            $count = $service->countsByType();
            var_dump($count);
        }
        return View::create(
            $this->dataSerialize(new PrimitiveArray([$count])),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get counts of feedback grouped by feedback category",
     *      parameters={
     *          {
     *              "name"="group_by",
     *              "requirement"="\w+",
     *              "description"="counts of feedback grouped by feedback category",
     *              "dataType"="string",
     *              "required"=true
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/feedback/counts", name="api_feedback_counts_by_type")
     * @return View
     * @throws \LogicException
     */
    public function getTypeCountsAction()
    {
        $count = $this->get('data.feedback')->countsByType();
        return View::create(
            $this->dataSerialize(new PrimitiveArray([$count])),
            Response::HTTP_OK
        );
    }
}
