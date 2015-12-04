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
use DeskPRO\Bundle\AppBundle\Entity\TaskAttachment;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskAttachmentsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of attachments",
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
     * @Get("/task_attachments", name="api_task_attachments")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $task_attachments = $this->getDoctrine()->getManager()->createQueryBuilder()->select('a')->from('App:TaskAttachment', 'a');

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new DoctrineORMAdapter($task_attachments));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a attachment",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the attachment",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskAttachment"
     * )
     * @Get("/task_attachments/{id}", name="api_task_attachments_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $attachment = $this->getAttachment($id);

        if (empty($attachment)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($attachment),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new attachment",
     *      input={"class"="task_attachment", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskAttachment"
     * )
     * @Post("/task_attachments", name="api_task_attachments_post")
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
        $attachment = new TaskAttachment($this->getUser());

        return $this->handleFormSubmission($request, $attachment);
    }

    /**
     * @APIDoc(
     *      description="delete an attachment",
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
     * @Delete("/task_attachments/{id}", name="api_task_attachments_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $attachment = $this->getAttachment($id);
        $this->getDoctrine()->getManager()->remove($attachment);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @param int $id
     *
     * @return TaskAttachment
     */
    protected function getAttachment($id)
    {
        $id         = (int) $id;
        $attachment = $this->getDoctrine()->getManager()->getRepository('App:TaskAttachment')->find($id);

        if (!$attachment) {
            throw $this->createNotFoundException();
        }

        return $attachment;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request        $request
     * @param TaskAttachment $attachment
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TaskAttachment $attachment)
    {
        $status = $attachment->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task_attachment', $attachment)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {

            /** @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage $bs */
            $bs = $this->container->getBlobStorage();

            $file_string  = base64_decode($submitted['file']);
            $file_name    = $submitted['filename'];
            $content_type = $submitted['content_type'];

            $blob = $bs->createBlobRecordFromString($file_string, $file_name, $content_type);

            $attachment->setBlob($blob);

            $this->getDoctrine()->getManager()->persist($attachment);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_task_attachments_get', array('id' => $attachment->getId()));

            return View::create(
                $this->dataSerialize($attachment),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }
}
