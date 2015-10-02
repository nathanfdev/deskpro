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
use FOS\RestBundle\Controller\Annotations\Get;
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
     * @return View
     * @throws \LogicException
     */
    public function cgetAction(Request $request)
    {
        /* @ToDo move below functionality into FeedbackComment repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('c')
            ->from('DeskPRO:FeedbackComment', 'c');
        $awaitingValidation = $request->get('awaiting_validation');
        if ($awaitingValidation) {
            $qb
                ->andWhere('c.status = :validating')
                ->setParameter('validating', FeedbackComment::STATUS_VALIDATING)
                ->orWhere('c.status = :visible AND c.is_reviewed = 0')
                ->setParameter('visible', FeedbackComment::STATUS_VISIBLE);
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
     * @return View
     * @throws \LogicException
     */
    public function counterAction(Request $request)
    {
        /* @ToDo move below functionality into FeedbackComment repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('f.id', 'count(c.id) as counter')
            ->from('DeskPRO:FeedbackComment', 'c')
            ->innerJoin('c.feedback', 'f');
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
     *
     * @throws AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
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

        if ($form->isValid()) {
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

        throw new InvalidFormException($form);
    }
}
