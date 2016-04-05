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

use Application\ImportBundle\Generator\Exporter\DeskPRO;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCountCriteria;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackSelectCriteria;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * API access to feedback.
 *
 * @ApiModes("all")
 */
class FeedbackController extends BaseController
{
    /**
     * With this endpoint you can fetch the list of feedback filtered and sorted with various options.
     *
     * @ApiDoc(
     *     section="Feedback",
     *     resourceDescription="Operations about feedback",
     *     tags={"feedback"="#4422bb"},
     *     description="get a filtered list of feedback",
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"},
     *         {"name"="awaiting_validation", "pattern"="1", "description"="select feedback awaiting validation only", "dataType"="boolean"},
     *         {"name"="status", "pattern"="active|closed|hidden", "description"="filter by status", "dataType"="string"},
     *         {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
     *         {"name"="status_category", "pattern"="\w|[\w]", "description"="filter by status category", "dataType"="string[]"},
     *         {"name"="category", "pattern"="\w|[\w]", "description"="category title, or titles array", "dataType"="string[]"},
     *         {"name"="custom_category", "pattern"="\w|[\w]", "description"="filter by custom category", "dataType"="string[]"},
     *         {"name"="labels_mode", "pattern"="any|all", "description"="how to load labels", "dataType"="string"},
     *         {"name"="label", "pattern"="\w,\w...\w", "description"="select feedback with given lables", "dataType"="string"},
     *         {"name"="no_labels", "pattern"="1", "description"="select feedback have no label", "dataType"="boolean"},
     *         {"name"="ids", "pattern"="\d,\d...\d", "description"="comma separated ids list", "dataType"="string"},
     *         {"name"="created_from", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s start", "dataType"="date"},
     *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="lmit by date, interval`s end", "dataType"="date"},
     *         {"name"="order_dir", "pattern"="date_created|total_rating|num_ratings|id|title|status|category|person", "description"="how to order result", "dataType"="string"},
     *         {"name"="order_by", "pattern"="asc|desc", "description"="order direction", "dataType"="string"},
     *     },
     *     statusCodes={
     *         200="Returned if successful request",
     *         400="Returned if you filter set was malformed",
     *     },
     *     output="array<Application\DeskPRO\Entity\Feedback>"
     * )
     * @Rest\Get("/feedback", name="api_feedback")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $dataService = $this->get('data.feedback');
        $params      = $this->removeAdditionalParameters($request);
        try {
            $criteria = FeedbackSelectCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 5);

        $feedback = $dataService->selectFeedback($criteria, $page, $count);

        return View::create(
            $this->wrap($feedback),
            Response::HTTP_OK
        );
    }

    /**
     * With this endpoint you can fetch the list of feedback counts filtered and grouped with various options.
     *
     * @ApiDoc(
     *     section="Feedback",
     *     resourceDescription="Operations about feedback",
     *     tags={"feedback"="#4422bb"},
     *     description="get feedback counts",
     *     statusCodes={
     *         200="Returned if successful request",
     *         400="Returned if you filter set was malformed",
     *     },
     *     filters={
     *         {"name"="group_by", "pattern"="status_category|hidden_status|category|custom_category", "description"="how to group counts", "dataType"="boolean"},
     *         {"name"="awaiting_validation", "pattern"="1", "description"="select feedback awaiting validation only", "dataType"="boolean"},
     *         {"name"="status", "pattern"="active|closed|hidden", "description"="filter by status", "dataType"="string"},
     *         {"name"="hidden_status", "dataType"="integer", "pattern"="unpublished|deleted|spam|draft", "description"="limit with hidden_status"},
     *         {"name"="status_category", "pattern"="\w|[\w]", "description"="filter by status category", "dataType"="string[]"},
     *         {"name"="category", "pattern"="\w|[\w]", "description"="category title, or titles array", "dataType"="string[]"},
     *         {"name"="custom_category", "pattern"="\w|[\w]", "description"="filter by custom category", "dataType"="string[]"},
     *         {"name"="labels_mode", "pattern"="any|all", "description"="how to load labels", "dataType"="string"},
     *         {"name"="label", "pattern"="\w,\w...\w", "description"="select feedback with given lables", "dataType"="string"},
     *         {"name"="no_labels", "pattern"="1", "description"="select feedback have no label", "dataType"="boolean"},
     *         {"name"="ids", "pattern"="\d,\d...\d", "description"="comma separated ids list", "dataType"="string"},
     *         {"name"="created_from", "pattern"="YYYY-mm-dd H:i:s", "description"="limit by date, interval`s start", "dataType"="date"},
     *         {"name"="created_to", "pattern"="YYYY-mm-dd H:i:s", "description"="lmit by date, interval`s end", "dataType"="date"},
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/feedback/counts", name="api_feedback_count")
     *
     * @param Request $request
     *
     * @throws \LogicException
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws BadRequestHttpException
     *
     * @return View
     */
    public function getCountsAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService $dataService */
        $dataService = $this->get('data.feedback');
        $params      = $this->removeAdditionalParameters($request);
        try {
            /** @var FeedbackCountCriteria $criteria */
            $criteria = FeedbackCountCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $count = $dataService->countFeedback($criteria);

        return View::create(
            $this->wrap($count),
            Response::HTTP_OK
        );
    }
}
