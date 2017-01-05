<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskCountsContrller.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks/counts")
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
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/groups")
     */
    public function getGroupCountsAction()
    {
        $dataService = $this->get('data.task_counts');

        $count = Count::fromGroupedBy('group');
        $count
            ->addNested($dataService->getAllCount(), null, 'all', null, true)
            ->addNested($dataService->getMyCount(), null, 'my')
            ->addNested($dataService->getTeamCount(), null, 'team')
            ->addNested($dataService->getDepartmentCount(), null, 'department')
            ->addNested($dataService->getDelegatedCount(), null, 'delegated')
            ->addNested($dataService->getUnassignedCount(), null, 'unassigned')
        ;

        return View::create($this->wrap($count));
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
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/agents")
     */
    public function getAgentCountsAction()
    {
        $count = Count::fromGroupedBy('agent');
        foreach ($this->get('data.task_counts')->getAgentsCounts() as $agentCount) {
            $count->addNested($agentCount['tasks_count'], $agentCount['id'], null, $agentCount['name'], true);
        }

        return View::create($this->wrap($count));
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
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/projects")
     */
    public function getProjectCountsAction()
    {
        $count = Count::fromGroupedBy('project');
        foreach ($this->get('data.task_counts')->getProjectsCounts() as $projectCount) {
            $count->addNested($projectCount['tasks_count'], $projectCount['id'], null, $projectCount['title'], true);
        }

        return View::create($this->wrap($count), Response::HTTP_OK);
    }
}
