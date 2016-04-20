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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks\Projects;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Tasks\TasksController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskProjectType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskProjectsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/task_projects")
 * @ApiDoc(target="all", section="TaskProjects", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\TaskProject")
 */
class TaskProjectsController extends CrudController
{
    public static $entity    = TaskProject::class;
    public static $type      = TaskProjectType::class;
    public static $listSort  = 'title';
    public static $listOrder = 'asc';

    /**
     * Fetch task list associated with the project specified by id.
     *
     * @ApiDoc(
     *     section="TaskProjects",
     *     resourceDescription="Operations about task projects",
     *     description="get tasks for the project with specified id",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the project", "dataType"="integer"}
     *     },
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\Task>"
     * )
     * @Rest\Get("/{id}/tasks", name="api_projects_tasks_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTasksAction(Request $request, $id)
    {
        return TasksController::subRequestSearch($this->getKernel(), $request, ['project' => $id]);
    }

    /**
     * @ApiDoc(
     *     section="TaskProjects",
     *     resourceDescription="Operations about task projects",
     *     description="get lists for a project",
     *     requirements={
     *         {"name"="projectId", "requirement"="\d+", "description"="the id of the project", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if everything is OK"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskList>"
     * )
     * @Rest\Get("/{project}/lists")
     *
     * @param TaskProject $project
     *
     * @return View
     */
    public function getListsAction(TaskProject $project)
    {
        return View::create($this->wrap($project->getLists()), Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $project_ids = $request->query->get('ids', []);
        $project_ids = array_map(function ($value) {
            return (int) $value;
        }, $project_ids);

        if (!empty($project_ids)) {
            $qb->andWhere("{$alias}.id IN (:project_ids)")->setParameter('project_ids', $project_ids);
        }
    }
}
