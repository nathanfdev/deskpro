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
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;

use Application\ApiBundle\Service\Dashboard as DashboardService;
use Application\ApiBundle\Service\DashboardPermissions as DashboardPermissionService;

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

    /**
     * {@inherited}
     */
    public function init()
    {
        parent::init();
        $this->service = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
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
            if ($this->permissionsService->isAllowedToEdit($this->person, $dashboard)) {
                $data[$k] = $this->service->getDashboardData($dashboard);
            }
//            $data[$k] = $this->_getDashboardData($dashboard);
//            $data[$k]['widgets'] = $this->_getDashboardWidgets($dashboard);
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
            if(!$this->permissionsService->isAllowedToEdit($this->person, $dashboard))
            {
                throw $this->createNotFoundException('Dashboard not found!');
            }
        } else {
            $dashboard = new Dashboard();
        }
        $title = $this->in->getCleanValue('title', 'string');
        $dashboard
            ->setTitle($title);
        $data = $this->service->saveDashboard($dashboard);
        $this->permissionsService->setPermissions($this->person, $dashboard, DashboardPermissionService::PERMISSION_FULL);
        return $this->createApiSuccessResponse($data);
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
        }

        return $this->service->saveDashboard($dashboard);
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
        if(!$this->permissionsService->isAllowedToView($this->person, $dashboard))
        {
            throw $this->createNotFoundException('Dashboard not found!');
        }
        $this->service->deleteDashboard($dashboard);

        return $this->createApiDeleteResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/dashboards/{id}/widgets",
     * 	@SWG\Operation(
     *      @SWG\ResponseMessage(code=404, message="Dashboard not found"),
     *      @SWG\ResponseMessage(code=404, message="Report not found"),
     *      @SWG\ResponseMessage(code=200, message="success"),
     * 		method="POST",
     * 		summary="Create new widget",
     * 		notes="Returns list of dashboards",
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
     *				name="col",
     *				description="Horizontal widget position",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *          @SWG\Parameter(
     *				name="row",
     *				description="Vertical widget position",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *          @SWG\Parameter(
     *				name="size_x",
     *				description="Widget width",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *          @SWG\Parameter(
     *				name="size_y",
     *				description="Widget height",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *          ),
     *      )
     * 	)
     * )
     */
    public function addWidgetAction($id)
    {
        /** @var Dashboard $dashboard */
        $dashboard = $this->service->getDashboard($id);
        /**
         * @var \Application\DeskPRO\Reports\Builder $reports_builder
         */
        $reports_builder = $this->container->getSystemService('reports_builder');
        $report          = $reports_builder->getById($this->in->getCleanValue('report', 'int'));
        if(!$report) {
            throw $this->createNotFoundException('Dashboard not found!');
        }

        $widget = new Widget();

        $widget
            ->setTitle($this->in->getCleanValue('name', 'string'))
            ->setSize(
                array
                (
                    $this->in->getCleanValue('size_x', 'int'),
                    $this->in->getCleanValue('size_y', 'int')
                )
            )
            ->setPosition(
                array(
                    $this->in->getCleanValue('row', 'int'),
                    $this->in->getCleanValue('col', 'int')
                )
            )
            ->setDashboard($dashboard)
            ->setReport($report);
        $this->em->persist($widget);
        $this->em->flush();

        return $this->createApiSuccessResponse($this->_getWidgetData($widget));
    }

    /**
     *
     * @throws NotFoundHttpException
     * @SWG\Api(
     * 	path="/dashboards/widgets/{id}",
     *
     * 	@SWG\Operation(
     *      @SWG\ResponseMessage(code=404, message="Widget not found"),
     *      @SWG\ResponseMessage(code=200, message="success"),
     * 		method="POST",
     * 		summary="Save dashboard with new parameters",
     *		type="array",
     *      @SWG\Parameters (
     *			@SWG\Parameter(
     *				name="id",
     *				description="Widget ID",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *            ),
     *          @SWG\Parameter(
     *				name="col",
     *				description="Horizontal widget position",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *          @SWG\Parameter(
     *				name="row",
     *				description="Vertical widget position",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *          @SWG\Parameter(
     *				name="size_x",
     *				description="Widget width",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *            ),
     *          @SWG\Parameter(
     *				name="size_y",
     *				description="Widget height",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     * 	)
     * )
     */
    public function saveWidgetAction($id)
    {
        if($id) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardStat')->find($id);
            if(!$widget) {
                throw $this->createNotFoundException('Widget not found!');
            }
        } else {
            $widget = new Widget();
        }
        list($sizeX, $sizeY) = array
        (
            $this->in->getCleanValue('size_x', 'int'),
            $this->in->getCleanValue('size_y', 'int')
        );
        list($row, $col) = array
        (
            $this->in->getCleanValue('row', 'int'),
            $this->in->getCleanValue('col', 'int')
        );
        $widget->setSize(array($sizeX, $sizeY))->setPosition(array($row, $col));
        $this->em->persist($widget);
        $this->em->flush();
        return $this->createApiSuccessResponse();
    }

    public function getWidgetDataAction($widget)
    {
        $widgetData = $this->_getWidgetData($widget);
        return $this->createApiResponse($widgetData);
    }

    public function getWidgetAction($id)
    {
        $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $id);
        return $this->createApiResponse($this->_getWidgetData($widget));
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @return Response
     * @SWG\Api(
     *    path="/dashboards/widgets/{id}",
     *
     * 	@SWG\Operation(
     *      @SWG\ResponseMessage(code=404, message="Widget not found"),
     *      @SWG\ResponseMessage(code=200, message="deleted"),
     *        method="DELETE",
     *        summary="Delete widget",
     *        type="array",
     *      @SWG\Parameters (
     *			@SWG\Parameter(
     *                name="id",
     *                description="Widget id",
     *                paramType="path",
     *                required=true,
     *                type="integer"
     *            ),
     *      )
     *    )
     * )
     */
    public function deleteWidgetAction($id)
    {
        $widget = $this->em->getRepository('DeskPRO:ReportDashboardStat')->find($id);
        if($widget) {
            $this->em->remove($widget);
            $this->em->flush();
            return $this->createApiDeleteResponse();
        } else {
            throw $this->createNotFoundException('Widget not found!');
        }
    }


}
