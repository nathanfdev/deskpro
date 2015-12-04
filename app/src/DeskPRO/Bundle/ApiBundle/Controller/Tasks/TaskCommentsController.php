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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Entity\TaskComment;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskCommentsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of comments",
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
     * @Get("/task_comments", name="api_task_comments")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $comments = $this->getDoctrine()->getManager()->createQueryBuilder()->select('c')
            ->from('App:TaskComment', 'c')->orderBy('c.date_created', 'ASC');

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new DoctrineORMAdapter($comments));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
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
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskComment"
     * )
     * @Get("/task_comments/{id}", name="api_task_comments_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $comment = $this->getTaskComment($id);

        if (empty($comment)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($comment),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new comment",
     *      input={"class"="comment", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskComment"
     * )
     * @Post("/task_comments", name="api_task_comments_post")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $comment = new TaskComment($this->getUser());

        return $this->handleFormSubmission($request, $comment);
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
     * @Put("/task_comments/{id}", name="api_task_comments_put")
     *
     * @param Request $request
     * @param $id
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $comment = $this->getTaskComment($id);

        return $this->handleFormSubmission($request, $comment);
    }

    /**
     * @APIDoc(
     *      description="delete a comment",
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
     * @Delete("/task_comments/{id}", name="api_task_comments_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $comment = $this->getTaskComment($id);
        $this->getDoctrine()->getManager()->remove($comment);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @APIDoc(
     *      description="get attachments for a comment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the comment",
     *              "dataType"="integer"
     *          }
     *      },
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
     *
     * @Get("/task_comments/{id}/attachments", name="api_task_comments_attachments_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        $comment = $this->getTaskComment($id);

        if (empty($comment)) {
            throw $this->createNotFoundException();
        }

        $attachments = $comment->getAttachments();

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($attachments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @param int $id
     *
     * @return TaskComment
     */
    protected function getTaskComment($id)
    {
        $id      = (int) $id;
        $comment = $this->getDoctrine()->getManager()->getRepository('App:TaskComment')->find($id);

        if (!$comment) {
            throw $this->createNotFoundException();
        }

        return $comment;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request     $request
     * @param TaskComment $comment
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TaskComment $comment)
    {
        $status = $comment->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task_comment', $comment)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_task_comments_get', array('id' => $comment->getId()));

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
