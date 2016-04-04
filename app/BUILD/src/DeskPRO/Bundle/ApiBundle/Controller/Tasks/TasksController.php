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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\Tasks\TasksSelectCriteria;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskType;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TasksController.
 *
 * @ApiDocSection("Tasks")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Task")
 * @ApiModes("all")
 * @Rest\Route("/tasks")
 */
class TasksController extends CrudController
{
    public static $entity = Task::class;
    public static $type   = TaskType::class;

    /**
     * You can provide additionaly "me" as value for creator, team, agent or department to fetch list of task related to
     * you, your command or department.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get a list of tasks",
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"},
     *         {"name"="label", "pattern"="(\w,)+", "description"="filter by labels", "dataType"="string"},
     *         {"name"="label_mode", "pattern"="+d+", "description"="additional filter for label, select where labels count > then you specified", "dataType"="string"},
     *         {"name"="project", "pattern"="(\d+),+", "description"="filter by project", "dataType"="string"},
     *         {"name"="creator", "pattern"="(\d+),+", "description"="filter by project", "dataType"="string"},
     *         {"name"="no_assignments", "pattern"="1|0", "description"="select only unassigned", "dataType"="boolean"},
     *         {"name"="assigned_agent", "pattern"="(\d+,)+", "description"="only where assigned agent has id", "dataType"="string"},
     *         {"name"="not_assigned_agent", "pattern"="(\d+,)+", "description"="only where assigned agent has no id", "dataType"="string"},
     *         {"name"="assigned_team", "pattern"="(\d+,)+", "description"="only where assigned team has id", "dataType"="string"},
     *         {"name"="not_assigned_team", "pattern"="(\d+,)+", "description"="only where assigned team has no id", "dataType"="string"},
     *         {"name"="assigned_department", "pattern"="(\d+,)+", "description"="only where assigned department has id", "dataType"="string"},
     *         {"name"="not_assigned_department", "pattern"="(\d+,)+", "description"="only where assigned department has no id", "dataType"="string"},
     *         {"name"="created_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by created date", "dataType"="string"},
     *         {"name"="created_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by created date", "dataType"="string"},
     *         {"name"="due_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by due date", "dataType"="string"},
     *         {"name"="due_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by due date", "dataType"="string"},
     *         {"name"="done_from", "pattern"="[a-zA-Z0-9\s-:]+", "description"="start of range to filter by done date", "dataType"="string"},
     *         {"name"="done_to", "pattern"="[a-zA-Z0-9\s-:]+", "description"="end of range to filter by done date", "dataType"="string"},
     *         {"name"="done", "pattern"="done", "description"="select only done|undone tasks", "dataType"="string"},
     *         {"name"="order_by", "pattern"="id|title|list|project|date_due|date_done|date_created|assignee", "description"="how to order", "dataType"="string"},
     *         {"name"="order_dir", "pattern"="asc|desc", "description"="order direction", "dataType"="string"},
     *
     *     },
     *     statusCodes={
     *         200="Returned if success",
     *         400="Returned if your request was malformed",
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Task>"
     * )
     * @Rest\Get("", name="api_tasks")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
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
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:Tasks\Tasks:list',
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Get subtasks for task with given id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get subtasks for a task",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *          200="Returned if everything is ok",
     *          404="We will return this status if task with specified id was not found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskSubtask>"
     * )
     *
     * @Rest\Get("/{id}/subtasks", name="api_tasks_subtasks_get")
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

        return View::create($this->wrap($sub_tasks), Response::HTTP_OK);
    }

    /**
     * Get comments for task with given id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get comments for a task",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if success",
     *         404="We will return this status if task with specified id was not found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskComment>"
     * )
     *
     * @Rest\Get("/{id}/comments", name="api_tasks_comments_get")
     *
     * @param Request $request
     * @param int     $id
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

        return View::create($this->wrap($pager), Response::HTTP_OK);
    }

    /**
     * Get attachments for task with given id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attachments for a task",
     *     requirements={
     *         { "name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskAttachment>"
     * )
     *
     * @Rest\Get("/{id}/attachments", name="api_tasks_attachments_get")
     *
     * @param Request $request
     * @param int     $id
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

        return View::create($this->wrap($pager), Response::HTTP_OK);
    }

    /**
     * Get attached tickets for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached tickets",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/tickets", name="api_tasks_linked_tickets_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getLinkedTicketsAction(Request $request, $id)
    {
        return $this->getLinked($request, $id, 'tickets');
    }

    /**
     * Get attached chats for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached chats",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     input={"class"="task", "name"=""},
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/chats", name="api_tasks_linked_chats_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getLinkedChatsAction(Request $request, $id)
    {
        return $this->getLinked($request, $id, 'chats');
    }

    /**
     * Get attached chats for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached articles",
     *     section="Tasks",
     *     description="get attached links for a task",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/articles", name="api_tasks_linked_articles_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getLinkedArticlesAction(Request $request, $id)
    {
        return $this->getLinked($request, $id, 'articles');
    }

    /**
     * @param Request $request
     * @param int     $id
     * @param string  $type
     *
     * @return View
     */
    protected function getLinked(Request $request, $id, $type)
    {
        $task = $this->findEntity($id, $request);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }
        $method = 'getLinked'.ucfirst($type);
        $links  = $task->$method();

        return View::create($this->wrap($links), Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        return new Task($this->getUser());
    }
}
