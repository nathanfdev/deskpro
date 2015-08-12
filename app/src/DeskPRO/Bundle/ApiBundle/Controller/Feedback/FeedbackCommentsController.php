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

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;

/**
 * API access to feedback comments.
 */
class FeedbackCommentsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="create a new comment",
     *      input={"class"="comment", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\FeedbackComment"
     * )
     * @Post("/task_comments", name="api_task_comments_post")
     * @param Request $request
     * @throws InvalidFormException
     * @throws \LogicException
     * @return View
     */
    public function postAction(Request $request)
    {
        $comment = new FeedbackComment();
        return $this->handleFormSubmission($request, $comment);
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
     * @return View
     * @throws \LogicException
     */
    public function getCountAwaitingValidationAction()
    {
        $count = $this->get('data.feedback_comments')->countAwaitingValidation();
        return View::create(
            $this->dataSerialize(new PrimitiveArray([$count])),
            Response::HTTP_OK
        );
    }

    /**
     * Will be abstracted for use by other controllers
     * @param Request $request
     * @return View
     * @throws AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
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
