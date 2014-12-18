<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReport;
use Application\ApiBundle\Service\Dashboard as DashboardService;
use Application\ApiBundle\Service\DashboardPermissions as DashboardPermissionsService;

/**
 * @SWG\Resource(
 * 	resourcePath="/dashboards/permissions",
 * 	description="Operations about Dashboard Permissions",
 * 	basePath="/api"
 * )
 */
class DashboardPermissionsController extends AbstractController
{

    /** @var DashboardPermissionsService */
    protected $permissionsService;

    /** @var DashboardService */
    protected $service;

    public function init()
    {
        parent::init();
        $this->service = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
    }

    /**
     * @SWG\Api(
     * 	path="/dashboards/permissions/{$id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get all dashboard permissions",
     *		type="array",
 *          @SWG\Parameters (
     *			@SWG\Parameter(
     *				name="id",
     *				description="Dashboard id",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *            ),
     *      )
     * 	)
     * )
     */

    public function listAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        if(!$this->permissionsService->isAllowedToView($this->person, $dashboard)) {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        return $this->createApiSuccessResponse($permissions = $this->permissionsService->getDashboardPermissions($dashboard));
    }

    public function cloneAction($id, $dashboard_id)
    {
        $prototype = $this->service->getReport($id);
        $dashboard = $this->service->getDashboard($dashboard_id);
        $report = new DashboardReport();
        $report
            ->setTitle($prototype->getTitle().'_clone')
            ->setColumns($prototype->getColumns())
            ->setDashboard($dashboard)
            ->setSortOrder($this->service->getLastSortOrder($dashboard));
        return $this->createApiSuccessResponse($this->service->saveReport($report));
    }

    public function saveAction($id)
    {
        $report = $this->service->getReport($id);

        $title = $this->in->getCleanValue('title', 'string');
        $columns = $this->in->getCleanValue('columns', 'string');

        $report
            ->setTitle($title)
            ->setColumns($columns);

        return $this->createApiSuccessResponse($this->service->saveReport($report));
    }

    public function createAction($dashboard_id)
    {
        $dashboard = $this->service->getDashboard($dashboard_id);
        $report = new DashboardReport();
        $title = $this->in->getCleanValue('title', 'string');
        $columns = $this->in->getCleanValue('columns', 'string');

        $report
            ->setTitle($title)
            ->setDashboard($dashboard)
            ->setSortOrder($this->service->getLastSortOrder($dashboard))
            ->setColumns($columns);
        return $this->createApiSuccessResponse($this->service->saveReport($report));

    }

    public function deleteAction($id)
    {
        $report = $this->service->getReport($id);
        $this->em->remove($report);
        $this->em->flush();
        return $this->createApiDeleteResponse();
    }
}
