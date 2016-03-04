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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackCommentsSelectCriteria;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * API access to feedback comments.
 *
 * @ApiModes("all")
 */
class FeedbackCommentController extends BaseController
{
    /**
     * Fetch all feedback comments list.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
     *      description="get list of feedback comments",
     *      statusCodes={
     *          200="Returned if everything is ok",
     *          400="Returned if your filters was invalid"
     *      },
     *      filters={
     *          {
     *              "name"="page",
     *              "type"="integer",
     *              "default"=1,
     *              "description"="current page",
     *          },
     *          {
     *              "name"="count",
     *              "type"="integer",
     *              "default"=5,
     *              "description"="per page comments quantity",
     *          },
     *          {
     *              "name"="awaiting_validation",
     *              "type"="boolean",
     *              "description"="set it if you want to fetch new comments",
     *          },
     *          {
     *              "name"="ids",
     *              "dataType"="string",
     *              "description"="a comma separated list of comment`s ids",
     *          },
     *          {
     *              "name"="category",
     *              "dataType"="string",
     *              "description"="category to search, exact name"
     *          },
     *          {
     *              "name"="statusCategory",
     *              "dataType"="integer",
     *              "description"="integer represents status category",
     *          },
     *          {
     *              "name"="label",
     *              "dataType"="string",
     *              "description"="a comma separated list of exact label names",
     *          },
     *          {
     *              "name"="no_labels",
     *              "dataType"="boolean",
     *              "description"="boolean value",
     *          },
     *          {
     *              "name"="custom_category",
     *              "dataType"="string[]",
     *              "description"="an array of exact custom categories names",
     *          },
     *          {
     *              "name"="status",
     *              "dataType"="integer",
     *              "description"="an integer value represents current status",
     *          },
     *          {
     *              "name"="hidden_status",
     *              "dataType"="string",
     *              "description"="an integer value represents current hidden_status",
     *          },
     *          {
     *              "name"="created_from",
     *              "dataType"="datetime",
     *              "description"="a datetime string to search comments since",
     *          },
     *          {
     *              "name"="created_to",
     *              "dataType"="datetime",
     *              "description"="a datetime string to search comments until",
     *          },
     *      },
     *      output={
     *        "class"="<Application\DeskPRO\Entity\FeedbackComment>"
     *      }
     * )
     * @FOS\View(serializerEnableMaxDepthChecks=true, serializerGroups={"feedback"})
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
            $this->dataSerialize($comments),
            Response::HTTP_OK
        );
    }

    /**
     * Count overall feedback comments or count for given feedbacks.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
     *      description="get counter of comments for feedback",
     *      statusCodes={
     *          200="Success"
     *      },
     *      filters={
     *          {
     *              "name"="ids",
     *              "dataType"="string",
     *              "description"="a comma separated list of feedback ids",
     *          }
     *     }
     * )
     * @FOS\View(serializerEnableMaxDepthChecks=true, serializerGroups={"feedback"})
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
            $this->dataSerialize($comments),
            Response::HTTP_OK
        );
    }

    /**
     * Get a specific comment.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
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
     *          200="Returned if comment was found",
     *          404="Returned if comment with specified id wasn't found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\FeedbackComment"
     * )
     * @FOS\View(serializerEnableMaxDepthChecks=true, serializerGroups={"feedback"})
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
     * The endpoint gives you an ability to modify comments status, content and  'status',.
     *
     * @APIDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
     *      description="update a comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the comment",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="status",
     *              "requirement"="hidden|visible",
     *              "description"="text representation of comment status",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="is_reviewed",
     *              "requirement"="0|1|true|false",
     *              "description"="is comment was reviewed",
     *              "dataType"="integer|boolean"
     *          },
     *          {
     *              "name"="content",
     *              "requirement"=".*",
     *              "description"="comment message",
     *              "dataType"="string"
     *          },
     *      },
     *      input={"class"="DeskPRO\Bundle\ApiBundle\Form\Type\FeedbackCommentType", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/feedback_comments/{id}", name="api_feedback_comments_put", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param int     $id
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
     * This endpoint gives you an ability do delete exactly one feedback comment.
     *
     * @APIDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
     *      description="delete feedback comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of comment to delete",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if comment was successfuly deleted",
     *          404="Returned if comment with specified id was not found"
     *      }
     * )
     * @Delete("/feedback_comments", name="api_feedback_comments_delete", requirements={"id": "\d+"})
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return View
     */
    public function deleteAction(Request $request)
    {
        $em  = $this->getDoctrine()->getManager();
        $ids = $request->get('id');
        $qb  = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->andWhere('c.id IN (:ids)')
            ->setParameter('ids', $ids);

        $comments = $qb->getQuery()->getResult();
        if (!$comments) {
            throw $this->createNotFoundException();
        }
        foreach ($comments as $comment) {
            $em->remove($comment);
        }

        $em->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * This endpoint gives you an ability to approve comments with given ids.
     *
     * @APIDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
     *      description="approve comments",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[\d+]",
     *              "array"=true,
     *              "description"="an array of comment ids",
     *              "dataType"="[integer]"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned if everything was OK",
     *      }
     * )
     *
     * @param Request $request
     * @Patch("/feedback_comments/approve", name="feedback_comments_approve_mass_action")
     *
     * @return View
     */
    public function massApproveAction(Request $request)
    {
        $em  = $this->getDoctrine()->getManager();
        $ids = $request->get('id');
        $qb  = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->andWhere('c.id IN (:ids)')
            ->setParameter('ids', $ids);
        $comments = $qb->getQuery()->getResult();

        foreach ($comments as $comment) {
            $comment->setStatus(FeedbackComment::STATUS_VISIBLE);
        }
        $em->flush();

        return View::create(
            $this->dataSerialize([]),
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Fetch a list of feedback comments awaiting validation.
     *
     * @ApiDoc(
     *      section="Feedback",
     *      tags={"feedback"="#4422bb", "comments"},
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
            $this->dataSerialize($count),
            Response::HTTP_OK
        );
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request         $request
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
