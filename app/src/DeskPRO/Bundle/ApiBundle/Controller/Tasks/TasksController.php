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
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\DataService\Tasks\TasksSelectCriteria;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TasksController.
 */
class TasksController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of tasks",
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
     * @Get("/tasks", name="api_tasks")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        try {
            $params   = $request->query->all();
            $criteria = TasksSelectCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 50);

        $tasks = $this->get('data.tasks')->selectTasks($criteria, $page, $count);

        return View::create($this->dataSerialize($tasks), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="update multiple tasks",
     *      input={"class"="task", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/tasks/mass", name="api_tasks_mass_put")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    public function massActionAction(Request $request)
    {
        $submitted = $request->request->all();
        if (empty($submitted['ids'])) {
            throw $this->createNotFoundException();
        }

        // We only need to validate the data for one task
        $taskIds = $submitted['ids'];
        unset($submitted['ids']);

        $task = $this->getTask($taskIds[0]);

        $this->validateForm($request, $task, $submitted);

        if (count($submitted)) {
            $dql = "UPDATE DeskPRO\Bundle\AppBundle\Entity\Task t SET";

            foreach ($submitted as $field => $value) {
                $dql .= ' t.'.$field.' = :'.$field;
            }

            $dql .= ' WHERE t.id IN (:ids)';

            /** @var \Doctrine\ORM\Query $query */
            $query = $this->getDoctrine()->getManager()->createQuery($dql);

            foreach ($submitted as $field => $value) {
                $query = $query->setParameter($field, $value);
            }

            $query = $query->setParameter('ids', $taskIds);
            $query->execute();
        }

        return View::create($this->dataSerialize($task), Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *      description="get a task",
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
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\Task"
     * )
     * @Get("/tasks/{taskId}", name="api_tasks_get")
     *
     * @param int $taskId
     *
     * @return View
     */
    public function getAction($taskId)
    {
        $task = $this->getTask($taskId);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($task), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="create a new task",
     *      input={"class"="task", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\Task"
     * )
     * @Post("/tasks", name="api_tasks_post")
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
        $task = new Task($this->getUser());

        return $this->handleFormSubmission($request, $task);
    }

    /**
     * @APIDoc(
     *      description="update a task",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="task", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/tasks/{id}", name="api_tasks_put")
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
        $task = $this->getTask($id);

        return $this->handleFormSubmission($request, $task);
    }

    /**
     * @APIDoc(
     *      description="delete a task",
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
     * @Delete("/tasks/{id}", name="api_tasks_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $task = $this->getTask($id);
        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get subtasks for a task",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/tasks/{id}/subtasks", name="api_tasks_subtasks_get")
     *
     * @param $id
     *
     * @return View
     */
    public function getSubtasksAction($id)
    {
        $task = $this->getTask($id);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $sub_tasks = $task->getSubtasks();

        return View::create($this->dataSerialize($sub_tasks), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get comments for a task",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
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
     * @Get("/tasks/{id}/comments", name="api_tasks_comments_get")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getCommentsAction(Request $request, $id)
    {
        $task = $this->getTask($id);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $comments = $task->getComments();

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($comments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create($this->dataSerialize($pager), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get attachments for a task",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
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
     * @Get("/tasks/{id}/attachments", name="api_tasks_attachments_get")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        $task = $this->getTask($id);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $comments = $task->getAttachments();

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($comments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create($this->dataSerialize($pager), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get attached links for a task",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/tasks/{id}/linked_items", name="api_tasks_links_get")
     *
     * @param $id
     *
     * @return View
     */
    public function getLinksAction($id)
    {
        $task = $this->getTask($id);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $links = $task->getLinkedItems();

        return View::create($this->dataSerialize($links), Response::HTTP_OK);
    }

    /**
     * Retrieve a single task.
     *
     * @param int $id
     *
     * @return Task
     */
    protected function getTask($id)
    {
        $task = $this->getDoctrine()->getManager()->getRepository('App:Task')->find((int) $id);
        if (!$task) {
            throw $this->createNotFoundException();
        }

        return $task;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request $request
     * @param Task    $task
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, Task $task)
    {
        $status = $task->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $submitted = $request->request->all();

        $this->validateForm($request, $task, $submitted);

        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        return View::create($this->dataSerialize($task), $status);
    }

    /**
     * Validate the form.
     *
     * @param Request $request   The request object
     * @param Task    $task      The task to update
     * @param array   $submitted The submitted data
     *
     * @throws InvalidFormException If form is invalid
     */
    protected function validateForm(Request $request, Task $task, $submitted)
    {
        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task', $task)->getForm();
        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }
    }
}
