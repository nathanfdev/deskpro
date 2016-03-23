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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Tasks\TasksSelectCriteria;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TasksController.
 *
 * @ApiModes("all")
 * @Route("/tasks")
 */
class TasksController extends CrudController
{
    public static $entity = Task::class;
    public static $type   = 'task';

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
     * @Get("", name="api_tasks")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        try {
            $params = $request->query->all();
            if (isset($params['include'])) {
                unset($params['include']);
            }
            $criteria = TasksSelectCriteria::fromParameters($params, new OptionsResolver(), [$this->getUser()]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 50);

        $tasks = $this->get('data.tasks')->selectTasks($criteria, $page, $count);

        return View::create($this->wrap($tasks), Response::HTTP_OK);
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
     * @Put("/mass", name="api_tasks_mass_put")
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

        $task = $this->findEntity($taskIds[0], $request);

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
     * @Get("/{id}/subtasks", name="api_tasks_subtasks_get")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function getSubtasksAction($id, Request $request)
    {
        $task = $this->findEntity($id, $request);
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
     * @Get("/{id}/comments", name="api_tasks_comments_get")
     *
     * @param Request $request
     * @param         $id
     *
     * @return View
     */
    public function getCommentsAction(Request $request, $id)
    {
        $task = $this->findEntity($id, $request);
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
     * @Get("/{id}/attachments", name="api_tasks_attachments_get")
     *
     * @param Request $request
     * @param         $id
     *
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        $task = $this->findEntity($id, $request);
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
     * @Get("/{id}/linked_items/{type}", name="api_tasks_links_get")
     *
     * @param Request $request
     * @param int     $id
     * @param string  $type
     *
     * @return View
     */
    public function getLinksAction(Request $request, $id, $type = 'tickets')
    {
        $task = $this->findEntity($id, $request);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }
        $method = 'getLinked'.ucfirst($type);
        $links  = $task->$method();

        return View::create($this->dataSerialize($links), Response::HTTP_OK);
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
    private function validateForm(Request $request, Task $task, $submitted)
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task', $task)->getForm();
        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        return new Task($this->getUser());
    }
}
