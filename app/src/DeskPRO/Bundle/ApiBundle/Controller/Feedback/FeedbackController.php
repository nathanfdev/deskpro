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

use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
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

/**
 * API access to feedback.
 */
class FeedbackController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a list of feedback",
     *      parameters={
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback", name="api_feedback")
     * @param Request $request
     * @return View
     * @throws \LogicException
     */
    public function cgetAction(Request $request)
    {
        /*$entityManager = $this->getDoctrine()->getManager();

        $datatype = $this->getDatatype($request);

        $tasks = $this->filterTasks($request, $entityManager);

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new DoctrineORMAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager, null, $datatype),
            Response::HTTP_OK
        );*/
    }


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
     * @throws \LogicException
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws BadRequestHttpException
     */
    public function getCountsAction(Request $request)
    {
        $dataService = $this->get('data.feedback');
        $params = $request->query->all();
        if (array_key_exists('group_by', $params) && $params['group_by'] === 'category') {
            $result = $dataService->countsByType();
            return View::create(
                $this->dataSerialize(new PrimitiveArray($result)),
                Response::HTTP_OK
            );
        }
        try {
            $criteria = FeedbackCountCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $count = $dataService->countFeedback($criteria);

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

}
