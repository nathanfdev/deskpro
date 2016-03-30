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
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\AgentGrouped;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\Grouped;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\ProjectGrouped;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskCountsContrller.
 *
 * @ApiModes("all")
 */
class TaskCountsController extends BaseController
{
    /**
     * Get list of grouped counts for tasks.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="Fetch tasks count grouped",
     *     resourceDescription="Operations about tasks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\Grouped"
     * )
     *
     * @Annotations\Get("/tasks/group_counts", name="api_task_group_counts")
     */
    public function getGroupCountsAction()
    {
        $count_service = $this->get('data.task_counts');
        $counts        = new Grouped();

        $counts
            ->setAll($count_service->getAllCount())
            ->setMy($count_service->getMyCount())
            ->setTeam($count_service->getTeamCount())
            ->setDepartment($count_service->getDepartmentCount())
            ->setDelegated($count_service->getDelegatedCount())
            ->setUnassigned($count_service->getUnassignedCount());

        return View::create($this->wrap($counts), Response::HTTP_OK);
    }

    /**
     * Get list of counts for tasks grouped by agent.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="Fetch tasks count grouped by agent",
     *     resourceDescription="Operations about tasks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\AgentGrouped>"
     * )
     *
     * @Annotations\Get("/tasks/agent_counts", name="api_task_agent_counts")
     */
    public function getAgentCountsAction()
    {
        $counts_res = $this->get('data.task_counts')->getAgentsCounts();
        $counts     = [];
        foreach ($counts_res as $count) {
            $counts[] = new AgentGrouped($count['agent_id'], $count['tasks_count']);
        }

        return View::create($this->wrap($counts), Response::HTTP_OK);
    }

    /**
     * Get list of counts for tasks grouped by agent.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="Fetch tasks count grouped by agent",
     *     resourceDescription="Operations about tasks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tasks\Counts\ProjectGrouped>"
     * )
     *
     * @Annotations\Get("/tasks/project_counts", name="api_task_project_counts")
     */
    public function getProjectCountsAction()
    {
        $counts_res = $this->get('data.task_counts')->getProjectsCounts();
        $counts     = [];
        foreach ($counts_res as $count) {
            $counts[] = new ProjectGrouped($count['project_id'], $count['tasks_count']);
        }

        return View::create($this->wrap($counts), Response::HTTP_OK);
    }
}
