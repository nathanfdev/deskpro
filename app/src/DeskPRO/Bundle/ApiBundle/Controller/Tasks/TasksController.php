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
        $filter = $this->getFilter($request);

        if (!empty($filter)) {
            $tasks = $this->getByAssignment($em, $filter);
        } else {
            $tasks = $em->getRepository('App:Task')->findAll();
        }

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createRepresentation($pager),
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
     * @param int $id
     * @return View
     */
    public function getAction($id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->createRepresentation($task),
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

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($subtasks->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createRepresentation($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @APIDoc(
     *      description="get labels for a task",
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
     * @Get("/tasks/{id}/labels", name="api_tasks_labels_get")
     *
     * @param Request $request
     * @param $id
     * @return View
     */
    public function getLabelsAction(Request $request, $id)
    {
        $task = $this->getTask($id);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $labels = $task->getLabels();

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($labels->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createRepresentation($pager),
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
            $this->createRepresentation($pager),
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
            $this->createRepresentation($pager),
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

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($links->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createRepresentation($pager),
            Response::HTTP_OK
        );
    }

    /**
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
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_tasks_get', array('id' => $task->getId()));

            return View::create(
                $this->createRepresentation($task),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getFilter(Request $request)
    {
        $filter = array();

        $allowedFilters = array(
            'assigned' => 'person',
            'assigned_team' => 'team',
            'assigned_department' => 'department',
            'creator' => 'creator',
        );

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
                    if (!empty($value) && $value !== 'null') {
                        // Clean the IDs, including those in a comma-separated string
                        $ids = explode(',', $value);
                        $ids = array_map(function($id) {
                            return (int) $id;
                        }, $ids);
                        $returnValue = implode(',', $ids);
                    }
                }

                // Add the not indicator back to the output
                if ($not) {
                    $returnValue = 'not_' . $returnValue;
                }

                $filter[$allowedFilters[$item]] = $returnValue;
            }
        }

        return $filter;
    }

    /**
     * @param $em
     * @param $filter
     * @return mixed
     */
    protected function getByAssignment($em, $filter)
    {
        // Get the entity manager for tasks, and join the assigned table
        /** @var EntityManager $em */
        $query = $em->createQueryBuilder()->select('t')
            ->from('App:Task', 't')
            ->leftJoin('t.assigned', 'a');

        // Loop through to set where query
        foreach ($filter as $field => $value) {

            // Work out whether there was a "not" indicator
            $not = false;
            if (strpos($value, 'not_') === 0) {
                $not = true;
                $value = substr($value, 4);
            }

            // Default to checking if null (or not null)
            $term = $not ? 'is NOT NULL' : 'is NULL';

            if (!is_null($value)) {
                // Otherwise, if it's an array check if it's in (or not in) the array
                $term = $not ? 'NOT IN (:' . $field . ')' : 'IN (:' . $field . ')';

                // If we don't have an array, check if it equals (or doesn't equal)
                if (strpos($value, ',') === false) {
                    $term = $not ? '!= :' : '= :';
                    $term .= $field;
                }

                // Set the parameter
                $query = $query->setParameter($field, $value);
            }

            // If the field is creator, we need the correct table
            $table = $field === 'creator' ? 't.' : 'a.';

            // Add the where clause to the query
            $query = $query->andWhere($table . $field . ' ' . $term);
        }

        $query = $query->getQuery();

        return $query->getResult();
    }
}
