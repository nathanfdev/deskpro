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
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskGroupsController.
 */
class TaskCountsController extends BaseController
{
    /**
     * @Get("/tasks/group_counts", name="api_task_group_counts")
     */
    public function getGroupCountsAction()
    {
        $count_service = $this->get('data.task_counts');
        $counts        = [
            'all'        => $count_service->getAllCount(),
            'my'         => $count_service->getMyCount(),
            'team'       => $count_service->getTeamCount(),
            'department' => $count_service->getDepartmentCount(),
            'delegated'  => $count_service->getDelegatedCount(),
            'unassigned' => $count_service->getUnassignedCount(),
        ];

        return View::create($this->createRepresentation($counts), Response::HTTP_OK);
    }

    /**
     * @Get("/tasks/agent_counts", name="api_task_agent_counts")
     */
    public function getAgentCountsAction()
    {
        $counts = $this->get('data.task_counts')->getAgentsCounts();

        return View::create($this->createRepresentation($counts), Response::HTTP_OK);
    }

    /**
     * @Get("/tasks/project_counts", name="api_task_project_counts")
     */
    public function getProjectCountsAction()
    {
        $counts = $this->get('data.task_counts')->getProjectsCounts();

        return View::create($this->createRepresentation($counts), Response::HTTP_OK);
    }
}
