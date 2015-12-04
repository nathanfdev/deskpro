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
use DeskPRO\Bundle\AppBundle\Entity\TaskSubtask;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskSubtasksController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of subtasks",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/subtasks", name="api_subtasks")
     *
     * @return View
     */
    public function cgetAction()
    {
        $query = $this->getDoctrine()->getManager()->createQueryBuilder()->select('s')->from('App:TaskSubtask', 's')
                                                                        ->orderBy('s.display_order', 'ASC');

        $subtasks = $query->getQuery()->getResult();

        return View::create(
            $this->dataSerialize($subtasks),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a subtask",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the subtask",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskSubtask"
     * )
     * @Get("/subtasks/{id}", name="api_subtasks_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $subtask = $this->getTaskSubtask($id);

        if (empty($subtask)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($subtask),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new subtask",
     *      input={"class"="subtask", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskSubtask"
     * )
     * @Post("/subtasks", name="api_subtasks_post")
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
        $subtask = new TaskSubtask($this->getUser());

        return $this->handleFormSubmission($request, $subtask);
    }

    /**
     * @APIDoc(
     *      description="update a subtask",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="subtask", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/subtasks/{id}", name="api_subtasks_put")
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
        $subtask = $this->getTaskSubtask($id);

        return $this->handleFormSubmission($request, $subtask);
    }

    /**
     * @APIDoc(
     *      description="delete a subtask",
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
     * @Delete("/subtasks/{id}", name="api_subtasks_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $subtask = $this->getTaskSubtask($id);
        $this->getDoctrine()->getManager()->remove($subtask);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @param int $id
     *
     * @return TaskSubtask
     */
    protected function getTaskSubtask($id)
    {
        $id      = (int) $id;
        $subtask = $this->getDoctrine()->getManager()->getRepository('App:TaskSubtask')->find($id);

        if (!$subtask) {
            throw $this->createNotFoundException();
        }

        return $subtask;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request     $request
     * @param TaskSubtask $subtask
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TaskSubtask $subtask)
    {
        $status = $subtask->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'subtask', $subtask)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($subtask);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_subtasks_get', array('id' => $subtask->getId()));

            return View::create(
                $this->dataSerialize($subtask),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }
}
