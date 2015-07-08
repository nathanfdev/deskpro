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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TasksController extends BaseController implements ClassResourceInterface
{
    /**
     * @Get("/tasks", name="api_tasks")
     * @return View
     */
    public function cgetAction()
    {
        $tasks = $this->getDoctrine()->getManager()->getRepository('App:Task')->findAll();

        $pager = new Pagerfanta(new ArrayAdapter($tasks));

        return View::create(
            $this->createRepresentation($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/tasks/{id}", name="api_tasks_get")
     * @param int $id
     * @return View
     */
    public function getAction($id)
    {
        $task = $this->getTask($id);

        return View::create(
            $this->createRepresentation($task),
            Response::HTTP_OK
        );
    }

    /**
     * @Post("/tasks", name="api_tasks_post")
     * @param Request $request
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     * @return View
     */
    public function postAction(Request $request)
    {
        $task = new Task();
        return $this->handleFormSubmission($request, $task);
    }

    /**
     * @param int $id
     * @return object
     */
    protected function getTask($id)
    {
        $id = (int) $id;
        $task = $this->getDoctrine()->getManager()->getRepository('App:Task')->find($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        return $task;
    }

    /**
     * @param Request $request
     * @param Task $task
     * @return View
     * @throws WrappedApiErrorException
     */
    protected function handleFormSubmission(Request $request, Task $task)
    {
        $status = $task->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $form = $this->get('form.factory')->createNamedBuilder(null, 'task', $task)->getForm();

        $submitted = $request->request->all();

        try {
            $form->submit($submitted, $request->getMethod() !== 'PUT');
        } catch (TermTypeDoesNotExistException $e) {
            throw new WrappedApiErrorException(
                new BadRequestHttpException(ApiErrors::TERM_TYPE_DOES_NOT_EXIST),
                array(
                    'type' => $e->getMessage()
                )
            );
        }

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush($task);

            return View::create(
                $this->createRepresentation($task),
                $status,
                array(
                    'Location' => $this->generateUrl('api_tasks_get', array('id' => $task->getId())),
                )
            );
        }

        var_dump('form not valid');
        foreach ($form->getErrors() as $error) {
            var_dump($error);
        }

        throw new InvalidFormException($form);
    }
}