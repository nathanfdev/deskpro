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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks\Projects;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\ApiBundle\Controller\Tasks\TasksController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Form\Type\ProjectMemberType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ProjectMembersController.
 *
 * @ApiDocSection("TaskProjects")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\ProjectMember")
 * @Rest\Route("/task_projects/{parentId}/members")
 * @ApiModes("all")
 */
class ProjectMembersController extends CrudSubController
{
    public static $entity          = ProjectMember::class;
    public static $type            = ProjectMemberType::class;
    public static $parentProperty  = 'project';
    public static $serializeMethod = 'wrap';

    /**
     * Fetch tasks list for project member.
     *
     * @ApiDoc(
     *      section="TaskProjects",
     *      resourceDescription="Operations about project members",
     *      description="get tasks for a member",
     *      requirements={
     *          {"name"="id", "requirement"="\d+", "description"="the id of the member", "dataType"="integer"}
     *      },
     *      filters={
     *          {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *          {"name"="count", "requirement"="\d+", "description"="results per page", "dataType"="integer"}
     *      },
     *      statusCodes={
     *          200="Success"
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Entity\Task>"
     * )
     * @Rest\Get("/{id}/tasks")
     *
     * @param Request       $request
     * @param ProjectMember $projectMember
     *
     * @return View
     */
    public function getTasksAction(Request $request, ProjectMember $projectMember)
    {
        $params = [
            'project' => $projectMember->getProject()->getId(),
        ];

        if ($projectMember->getPerson()) {
            $params['creator'] = $projectMember->getPerson()->getId();
        }
        if ($projectMember->getTeam()) {
            $params['assigned_team'] = $projectMember->getTeam()->getId();
        }
        if ($projectMember->getDepartment()) {
            $params['assigned_department'] = $projectMember->getDepartment()->getId();
        }

        return TasksController::subRequestSearch($this->getKernel(), $request, $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $type = $request->query->getAlpha('type');
        if ($type) {
            if (!in_array($type, ['person', 'team', 'department'])) {
                throw new BadRequestHttpException('Unknown member type');
            }

            $qb->andWhere("$alias.$type is not NULL");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'project' => $this->findParentOr404(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
