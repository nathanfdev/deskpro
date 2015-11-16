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

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to feedback comments.
 */
class FeedbackCommentController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get list of feedback comments",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback_comments_list", name="api_feedback_comments_list")
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        /* @ToDo move below functionality into FeedbackComment repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('c')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->innerJoin('c.feedback', 'feedback');
        $awaitingValidation = $request->get('awaiting_validation');
        $sort = $request->get('sort');
        $order = $request->get('order');
        $category = $request->get('category');
        $custom_category = $request->get('custom_category');
        $status = $request->get('status');
        $status_category = $request->get('status_category');
        $labels = $request->get('labels');
        if ($awaitingValidation) {
            $qb
                ->andWhere('c.status IN (:validating)')
                ->setParameter(
                    'validating',
                    [FeedbackComment::STATUS_VALIDATING, FeedbackComment::STATUS_USER_VALIDATING]
                )
                ->orWhere('c.is_reviewed = 0');
        }
        if ($sort && $order) {
            $qb->orderBy("c.$sort", $order);
        }
        if ($category) {
            $qb
                ->innerJoin('feedback.category', 'type')
                ->andWhere('type.title IN (:type)')
                ->setParameter('type', $category);
        }
        if ($custom_category) {
            $qb
                ->innerJoin('feedback.custom_data', 'category')
                ->andWhere('category.input IN (:category)')
                ->setParameter('category', $custom_category);
        }
        if ($status) {
            $qb
                ->andWhere('feedback.status IN (:status)')
                ->setParameter('status', $status);
        }
        if ($status_category) {
            $qb
                ->innerJoin('feedback.status_category', 'statusCategory')
                ->andWhere('statusCategory.title IN (:title)')
                ->setParameter('title', $status_category);
        }
        if ($labels) {
            $qb
                ->innerJoin('feedback.labels', 'labels')
                ->andWhere('labels.label IN (:labels)')
                ->setParameter('labels', $labels);
        }
        $comments = $qb->getQuery()->getResult();

        return View::create(
            $this->dataSerialize($comments),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get counter of comments for feedback",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/feedback_comments_counter", name="api_feedback_comments_counter")
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
            $this->createRepresentation($comments),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the comment",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\FeedbackComment"
     * )
     * @Get("/feedback_comments/{id}", name="api_feedback_comments_get", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $comment = $this->getFeedbackComment($id);

        return View::create(
            $this->dataSerialize($comment),
            Response::HTTP_OK
        );
    }

    /**
     * @APIDoc(
     *      description="update a comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="comment", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/feedback_comments/{id}", name="api_feedback_comments_put", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param         $id
     *
     * @throws WrappedApiErrorException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $comment = $this->getFeedbackComment($id);

        return $this->handleFormSubmission($request, $comment);
    }

    /**
     * @APIDoc(
     *      description="delete a feedback comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/feedback_comments/{id}", name="api_feedback_comments_delete", requirements={"id": "\d+"})
     *
     * @param $id
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $comment = $this->getFeedbackComment($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
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
     *          200="Success"
     *      }
     * )
     *
     * @Get("/feedback_comments/counts", name="api_feedback_comment_count")
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function getCountAwaitingValidationAction()
    {
        $count = $this->get('data.feedback_comments')->countAwaitingValidation();

        return View::create(
            $this->createRepresentation($count),
            Response::HTTP_OK
        );
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request $request
     * @param FeedbackComment $comment
     *
     * @throws AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws InvalidFormException
     *
     * @return View
     */
    private function handleFormSubmission(Request $request, FeedbackComment $comment)
    {
        $status = $comment->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'feedback_comment', $comment)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        $location = $this->generateUrl('api_feedback_comments_get', array('id' => $comment->getId()));

        return View::create(
            $this->dataSerialize($comment),
            $status,
            array(
                'Location' => $location,
            )
        );
    }

    /**
     * @param int $id
     *
     * @return FeedbackComment
     */
    private function getFeedbackComment($id)
    {
        $comment = $this->getDoctrine()->getManager()->getRepository('DeskPRO:FeedbackComment')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException();
        }

        return $comment;
    }
}
