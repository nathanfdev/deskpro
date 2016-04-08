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

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCommentsSelectCriteria;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * API access to feedback comments.
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback_comments")
 */
class FeedbackCommentController extends CrudController
{
    public static $entity    = FeedbackComment::class;
    public static $listOrder = 'asc';
    public static $type      = 'feedback_comment';

    /**
     * Fetch all feedback comments list.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"="#22aa22"},
     *      description="get list of feedback comments",
     *      statusCodes={
     *          200="Returned if everything is ok",
     *          400="Returned if your filters was invalid"
     *      },
     *      filters={
     *          {"name"="page", "type"="integer", "default"=1, "description"="current page"},
     *          {"name"="count", "type"="integer", "default"=5, "description"="per page comments quantity"},
     *          {"name"="awaiting_validation", "type"="boolean", "description"="set it if you want to fetch new comments"},
     *          {"name"="ids", "dataType"="string", "description"="a comma separated list of comment`s ids"},
     *          {"name"="category", "dataType"="string", "description"="category to search, exact name"},
     *          {"name"="statusCategory", "dataType"="integer", "description"="integer represents status category"},
     *          {"name"="label", "dataType"="string", "description"="a comma separated list of exact label names"},
     *          {"name"="no_labels", "dataType"="boolean", "description"="boolean value"},
     *          {"name"="custom_category", "dataType"="string[]", "description"="an array of exact custom categories names"},
     *          {"name"="status", "dataType"="integer", "description"="an integer value represents current status"},
     *          {"name"="hidden_status", "dataType"="string", "description"="an integer value represents current hidden_status"},
     *          {"name"="created_from", "dataType"="datetime", "description"="a datetime string to search comments since"},
     *          {"name"="created_to", "dataType"="datetime", "description"="a datetime string to search comments until"},
     *      },
     *      output="<Application\DeskPRO\Entity\FeedbackComment>"
     * )
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"details"})
     * @Rest\Get("/", name="api_feedback_comments_list")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCommentsDataService $dataService */
        $dataService = $this->get('data.feedback_comments');
        $params      = $this->removeAdditionalParameters($request);
        try {
            /** @var FeedbackCommentsSelectCriteria $criteria */
            $criteria = FeedbackCommentsSelectCriteria::fromParameters($params, new OptionsResolver());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $page     = $request->query->get('page', 1);
        $count    = $request->query->get('count', 5);
        $comments = $dataService->selectComments($criteria, $page, $count);

        return View::create(
            $this->wrap($comments),
            Response::HTTP_OK
        );
    }

    /**
     * Count overall feedback comments or count for given feedbacks.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"="#22aa22"},
     *      description="get counter of comments for feedback",
     *      statusCodes={
     *          200="Success"
     *      },
     *      filters={
     *          {"name"="ids", "dataType"="string", "description"="a comma separated list of feedback ids"}
     *     }
     * )
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"feedback"})
     * @Rest\Get("/counter", name="api_feedback_comments_counter")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function counterAction(Request $request)
    {
        /* @ToDo move below functionality into FeedbackComment repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        /* @var QueryBuilder $qb */
        $qb
            ->select('f.id', 'count(c.id) as counter')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->innerJoin('c.feedback', 'f')
            ->groupBy('f.id');
        $ids = $request->get('ids');
        if ($ids) {
            $qb
                ->andWhere('f.id IN (:ids)')
                ->setParameter('ids', explode(',', $ids));
        }

        $comments = $qb->getQuery()->getResult();

        return View::create(
            $this->wrap($comments),
            Response::HTTP_OK
        );
    }

    /**
     * Fetch a list of feedback comments awaiting validation.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"="#22aa22"},
     *      description="get count of feedback comment awaiting validation",
     *      parameters={
     *          {
     *              "name"="awaiting_validation",
     *              "requirement"="\d+",
     *              "description"="count of feedback comment awaiting validation",
     *              "dataType"="integer",
     *              "required"=true
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok"
     *      }
     * )
     *
     * @Rest\Get("/counts", name="api_feedback_comment_count")
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function getCountAwaitingValidationAction()
    {
        $count = $this->get('data.feedback_comments')->countAwaitingValidation();

        return View::create(
            $this->wrap($count),
            Response::HTTP_OK
        );
    }
}
