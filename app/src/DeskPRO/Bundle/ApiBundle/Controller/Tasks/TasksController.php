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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $datatype = $this->getDatatype($request);

        $tasks = $this->filterTasks($request, $em);

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new DoctrineORMAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createFractalRepresentation($pager, 'task', $datatype),
            Response::HTTP_OK
        );
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
     * @Get("/tasks/{id}", name="api_tasks_get")
     * @param Request $request
     * @param int $id
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->createFractalRepresentation($task, 'task'),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
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
     * @param Request $request
     * @param $id
     * @throws WrappedApiErrorException
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
     * @param $id
     * @return View
     */
    public function deleteAction($id)
    {
        $task = $this->getTask($id);
        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @param $id
     * @return View
     */
    public function getSubtasksAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $subtasks = $task->getSubtasks();

        return View::create(
            $this->createFractalRepresentation($subtasks, 'task'),
            Response::HTTP_OK
        );
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
     * @return View
     */
    public function getCommentsAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $comments = $task->getComments();

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($comments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createFractalRepresentation($pager, 'task_comment'),
            Response::HTTP_OK
        );
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
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $comments = $task->getAttachments();

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($comments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createFractalRepresentation($pager, 'task_attachment'),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @param $id
     * @return View
     */
    public function getLinksAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $links = $task->getLinkedItems();

        return View::create(
            $this->createFractalRepresentation($links, 'task_linked_item'),
            Response::HTTP_OK
        );
    }

    /**
     * Retrieve a single task
     * @param int $id
     * @return Task
     */
    protected function getTask($id)
    {
        $id = (int) $id;
        $task = $this->getDoctrine()->getManager()->getRepository('App:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException();
        }

        return $task;
    }

    /**
     * Will be abstracted for use by other controllers
     * @param Request $request
     * @param Task $task
     * @return View
     * @throws WrappedApiErrorException
     */
    protected function handleFormSubmission(Request $request, Task $task)
    {
        $status = $task->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;


        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task', $task)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_tasks_get', array('id' => $task->getId()));

            return View::create(
                $this->createFractalRepresentation($task, 'task'),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Get the datatype to use for creating the Fractal Representation
     * @param Request $request
     * @return int
     */
    protected function getDatatype(Request $request)
    {
        $params = $request->query->all();

        if (in_array('count_only', array_keys($params))) {
            return 3;
        }

        return 1;
    }

    /**
     * Retrieve tasks from the entity manager according to the request parameters
     * @param Request $request
     * @param $em
     * @return Query
     */
    protected function filterTasks(Request $request, $em)
    {
        $filter = $this->getFilter($request);

        if (empty($filter)) {
            /** @var EntityManager $em */
            return $em->createQueryBuilder()->select('t')
                ->from('App:Task', 't');
        }

        return $this->executeFilter($em, $filter);
    }

    /**
     * Calculate what terms to filter the request by
     * @param Request $request
     * @return array
     */
    protected function getFilter(Request $request)
    {
        $filter = [];

        $allowedFilters = [
            'assigned' => ['field' => 'person', 'table' => ['t.assigned', 'a']],
            'assigned_team' => ['field' => 'team', 'table' => ['t.assigned', 'a']],
            'assigned_department' => ['field' => 'department', 'table' => ['t.assigned', 'a']],
            'creator' => ['field' => 'creator'],
            'project' => ['field' => 'project'],
            'is_done' => ['field' => 'is_done'],
            'label' => ['field' => 'id', 'table' => ['t.labels', 'l']],
        ];

        foreach($request->query->all() as $item => $value) {
            if (in_array($item, array_keys($allowedFilters))) {
                // Add a NOT indicator
                $not = false;
                if (strpos($value, 'not_') === 0) {
                    $not = true;
                    $value = substr($value, 4);
                }

                // Default to null
                $returnValue = null;

                // If the value is set to 'me', get the current user's ID, teams and departments
                if ($value === 'me') {
                    $user = $this->getUser();
                    switch($allowedFilters[$item]) {
                        case 'team':
                            $returnValue = implode(',', $user->getTeamIds());
                            break;
                        case 'department':
                            // TODO perm_check
                            $user->loadHelper('AgentPermissions');
                            $returnValue = implode(',', $user->getAllowedDepartments());
                            break;
                        default:
                            $returnValue = $user->getId();
                    }
                } else {
                    if (!empty($value) && !in_array($value, ['null', 'false', 'true'])) {
                        // Clean the IDs, including those in a comma-separated string
                        $ids = explode(',', $value);
                        $ids = array_map(function($id) {
                            return (int) $id;
                        }, $ids);
                        $returnValue = implode(',', $ids);
                    } else if (!empty($value) && ($value === 'false' || $value === 'true')) {
                        $returnValue = ($value === 'true');
                    }
                }

                // Add the not indicator back to the output
                if ($not) {
                    $returnValue = 'not_' . $returnValue;
                }

                $filter[$item] = $allowedFilters[$item];
                $filter[$item]['value'] = $returnValue;
            }
        }

        return $filter;
    }

    /**
     * Build up a query according to the filter we need to process
     * @param $em
     * @param $filter
     * @return Query
     */
    protected function executeFilter($em, $filter)
    {
        /** @var EntityManager $em */
        $query = $em->createQueryBuilder()->select('t')
            ->from('App:Task', 't');

        // Join the necessary tables
        $joins = [];
        foreach ($filter as $param => $details) {
            if (!empty($details['table']) && !in_array($details['table'][0], $joins)) {
                $query = $query->leftJoin($details['table'][0], $details['table'][1]);
                $joins[] = $details['table'][0];
            }
        }

        // Set the where queries
        foreach ($filter as $param => $details) {
            // Work out whether there was a "not" indicator
            $not = false;
            if (strpos($details['value'], 'not_') === 0) {
                $not = true;
                $details['value'] = substr($details['value'], 4);
            }

            // Filter out null values
            $term = $not ? 'is NOT NULL' : 'is NULL';

            // If not null, see what kind of query it is
            if (!is_null($details['value'])) {
                $term = $not ? 'NOT IN (:' . $details['field'] . ')' : 'IN (:' . $details['field'] . ')';

                // If we don't have an array, check if it equals or doesn't equal
                if (strpos($details['value'], ',') === false) {
                    $term = $not ? '!= :' : '= :';
                    $term .= $details['field'];
                }

                // Set the query parameter
                $query = $query->setParameter($details['field'], $details['value']);
            }

            // Set the table to check
            $table = !empty($details['table']) ? $details['table'][1] : 't';
            $field = $table . '.' . $details['field'];

            // Put together the where clause
            $query = $query->andWhere($field . ' ' . $term);
        }

        return $query->getQuery();
    }
}
