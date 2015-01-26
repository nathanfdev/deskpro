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
use Application\DeskPRO\Entity\ReportDashboard as Dashboard;
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;

use Application\ApiBundle\Service\Dashboard as DashboardService;
use Application\ApiBundle\Service\DashboardPermissions as DashboardPermissionService;
use Application\ApiBundle\Service\DashboardWidget as DashboardWidgetService;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* @SWG\Resource(
* 	resourcePath="/dashboards",
* 	description="Operations about Dashboards",
* 	basePath="/api"
* )
*/
class DashboardController extends AbstractController
{

    /** @var DashboardService */
    protected $service;

    /** @var DashboardPermissionService */
    protected $permissionsService;

    /** @var DashboardWidgetService */
    protected $widgetService;

    /**
     * {@inherited}
     */
    public function init()
    {
        parent::init();
        $this->service = $this->get('dashboard.service');
        $this->widgetService = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
        $this->widgetService = $this->get('dashboard.widget.service');
    }


	/**
	 * @SWG\Api(
	 * 	path="/dashboards",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Search for tasks matching criteria",
	 * 		notes="Returns list of dashboards",
	 *		type="array",
	 * 	)
	 * )
	 */

    public function listAction()
    {
        $data = array();
        $dashboards = $this->em->getRepository('DeskPRO:ReportDashboard')->findAll();
        foreach ($dashboards as $k => $dashboard) {
            /** @var Dashboard $dashboard */
            if ($this->permissionsService->isAllowedToView($this->person, $dashboard)) {
                $data[$k] = $this->service->getDashboardData($dashboard);
            }
        }

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        if(!$this->permissionsService->isAllowedToView($this->person, $dashboard))
        {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $data = $this->service->getDashboardData($dashboard);
        $data['loaded'] = true;
        $data['reports'] = $this->service->getReportsData($dashboard);
        $data['permissions'] = $this->permissionsService->getApiDashboardPermissions($dashboard);
        return $this->createApiResponse($data);
    }


    /**
     *
     * @throws NotFoundHttpException
     * @SWG\Api(
     * 	path="/dashboards",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Create new dashboard",
     * 		notes="Returns list of dashboards",
     *		type="array",
     *          @SWG\Parameters (
     *			@SWG\Parameter(
     *				name="title",
     *				description="Dashboard name",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *            @SWG\Parameter(
     *				name="columns",
     *				description="Dashboard width in columns",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     * 	)
     * )
     * @SWG\Api(
     * 	path="/dashboards/{id}",
     *
     * 	@SWG\Operation(
     *      @SWG\ResponseMessage(code=404, message="Dashboard not found"),
     *      @SWG\ResponseMessage(code=200, message="success"),
     * 		method="POST",
     * 		summary="Save dashboard with new parameters",
     *		type="array",
     *      @SWG\Parameters (
     *			@SWG\Parameter(
     *				name="id",
     *				description="Dashboard id",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *            ),
     *          @SWG\Parameter(
     *				name="title",
     *				description="Dashboard name",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *          @SWG\Parameter(
     *				name="columns",
     *				description="Dashboard width in columns",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     * 	)
     * )
     */
    public function saveAction($id)
    {
        if($id) {
            $dashboard = $this->service->getDashboard($id);

            if( !$this->permissionsService->isAllowedToEdit($this->person, $dashboard))
            {
                throw $this->createNotFoundException('Dashboard not found!');
            }
        } else {
            $dashboard = new Dashboard();
        }
        // We have to save only permissions here if dashboard is not editable
        $postData = $this->in->getAll('post');
        if ($this->permissionsService->isEditableDashboard($dashboard)) {
            $dashboard
                ->setTitle($postData['title']);
            foreach($postData['reports'] as $report) {
                if(isset($report['deleted']) && $report['deleted']) {
                    $reportEntity = $this->service->getReport($report['id']);
                    $dashboard->removeReport($reportEntity);
                    $this->service->deleteReport($reportEntity);
                } else {
                    if(isset($report['id'])) {
                        $reportEntity = $this->service->getReport($report['id']);
//                    $reportEntity->setSortOrder($report['sort_order']);
                    } else {
                        $reportEntity = new Tab();
                        $reportEntity->setColumns(10)
                            ->setDashboard($dashboard);
                        $reportEntity->setSortOrder($this->service->getLastSortOrder($dashboard));
                        $dashboard->addReport($reportEntity);
                    }
                    if(isset($report['prototype_id'])) {
                        $report_prototype = $this->service->getReport($report['prototype_id']);
                        $this->widgetService->copyWidgetLinks($reportEntity,$report_prototype);
                    }
                    $reportEntity->setTitle($report['title']);
                    $this->service->saveReport($reportEntity);
                }
            }
        }
        $returnData = $this->service->saveDashboard($dashboard);
        $returnData['reports'] = $this->service->getReportsData($dashboard);
        if(isset($postData['permissions'])) {
            foreach($postData['permissions'] as $permission) {
                $agent = $this->permissionsService->getAgent($permission['id']);
                $this->permissionsService->setPermissions($agent, $dashboard, $permission['permissions']);
            }
        }
        $this->permissionsService->setPermissions($this->person, $dashboard, DashboardPermissionService::PERMISSION_FULL);
        $returnData['permissions'] = $this->permissionsService->getApiDashboardPermissions($dashboard);
        return $this->createApiSuccessResponse($returnData);
    }

    /**
     * @param $id
     * @return array
     */
    public function cloneAction($id)
    {
        $prototype = $this->service->getDashboard($id);
        if(!$this->permissionsService->isAllowedToView($this->person, $prototype))
        {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $dashboard = new Dashboard();
        $dashboard -> setTitle($prototype->getTitle().'_clone');

        foreach($prototype->getReports() as $report_prototype)
        {
            $report = new Tab();
            $report
                ->setTitle($report_prototype->getTitle().'_clone')
                ->setColumns($report_prototype->getColumns())
                ->setSortOrder($report_prototype->getSortOrder());
            $dashboard->addReport($report);
            $this->widgetService->copyWidgetLinks($report, $report_prototype);

        }
        $data = $this->service->saveDashboard($dashboard);

        $this->permissionsService->clonePermissions($dashboard, $prototype);

        return $this->createApiResponse($data);
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @return Response
     * @SWG\Api(
     *    path="/dashboards/{id}",
     *
     * 	@SWG\Operation(
     *      @SWG\ResponseMessage(code=404, message="Dashboard not found"),
     *      @SWG\ResponseMessage(code=200, message="deleted"),
     *        method="DELETE",
     *        summary="Delete dashboard with all widgets contains",
     *        type="array",
     *      @SWG\Parameters (
     *			@SWG\Parameter(
     *                name="id",
     *                description="Dashboard id",
     *                paramType="path",
     *                required=true,
     *                type="integer"
     *            ),
     *      )
     *    )
     * )
     */
    public function deleteAction($id)
    {
        $dashboard = $this->service->getDashboard($id);
        if(
            !$this->permissionsService->isAllowedToEdit($this->person, $dashboard)
            ||
            $this->permissionsService->isEditableDashboard($dashboard)
        )
        {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $this->service->deleteDashboard($dashboard);

        return $this->createApiDeleteResponse();
    }
}
