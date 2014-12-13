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
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Application\DeskPRO\Dpql\Statement\Display;

/**
* @SWG\Resource(
* 	resourcePath="/dashboards",
* 	description="Operations about Dashboards",
* 	basePath="/api"
* )
*/
class DashboardController extends AbstractController
{
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
//            $data[$k] = $this->_getDashboardData($dashboard);
//            $data[$k]['widgets'] = $this->_getDashboardWidgets($dashboard);


            $data[$k] = $this->_getDashboardData($dashboard);

        }

        return $this->createApiResponse($data);
    }

    public function getWidgetDataAction($widget)
    {
        $widgetData = $this->_getWidgetData($widget);
        return $this->createApiResponse($widgetData);
    }



    public function getAction($id)
    {
        $dashboard = $this->_getDashboard($id);
        $data = $this->_getDashboardData($dashboard);
        $data['loaded'] = true;
        $data['reports'] = $this->_getReportsData($dashboard);
        return $this->createApiResponse($data);
    }

    public function getWidgetAction($id)
    {
        $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $id);
        return $this->createApiResponse($this->_getWidgetData($widget));
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
            $dashboard = $this->_getDashboard($id);
        } else {
            $dashboard = new Dashboard();
        }
        $columns = $this->in->getCleanValue('columns', 'int');
        $title = $this->in->getCleanValue('title', 'string');
        $dashboard
            ->setTitle($title)
            ->setColumns($columns);
        $this->em->persist($dashboard);
        $this->em->flush();
        return $this->createSuccessResponse(array(
            'id' => $dashboard->getId(),
            'title' => $dashboard->getTitle(),
            'columns' => $dashboard->getColumns(),
        ));
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
        $dashboard = $this->_getDashboard($id);
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
        $dashboard = $this->_getDashboard($id);
        if($dashboard) {
            $this->em->remove($dashboard);
            $this->em->flush();
            return $this->createApiDeleteResponse();
        }
    }

    // service methods
    //todo: move in service

    protected function _getWidgetData($widget)
    {
        if(! ($widget instanceof Widget)) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget);
        }
        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $report = $widget->getReport();
        $widget_data = Display::renderQuery('json', $report->query);

        $data = array(
            'id'    => $widget->getId(),
            'name'  => $widget->getTitle(),
            "row"   => $pos[0],
            "col"   => $pos[1],
            "sizeX" => $size[0],
            "sizeY" => $size[1],
            "type"  => "graph",
            "data"  => $widget_data,
        );
        return $data;
    }

    protected function _getReportsData($dashboard)
    {
        $dashboard = $this->_getDashboard($dashboard);

        $reports_data = array();
        foreach ($dashboard->getReports() as $report)
        {
//            $widgets = array();
//            foreach($report->getWidgets() as $widget) {
//                $pos  = $widget->getPosition();
//                $size = $widget->getSize();
//                $widgets[] = array(
//                    'id'    => $widget->getId(),
//                    'name'  => $widget->getTitle(),
//                    "row"   => $pos[0],
//                    "col"   => $pos[1],
//                    "sizeX" => $size[0],
//                    "sizeY" => $size[1],
//                    "type"  => "graph",
//                );
//            }
            $data = array(
                'title'        => $report->getTitle(),
                'id'           => $report->getId(),
                'dashboard_id' => $dashboard->getId(),
                'loaded'       => false,
                'options'      => array(
                    'columns'  => $report->getColumns(),
                    "floating" => false,
                    "swapping" => false,
                ),
                //                'widgets'  => $widgets,
            );
            $reports_data[] = $data;
        }


        return $reports_data;
    }

    protected function _getDashboardWidgets($dashboard)
    {
        $widgets = array();

        foreach($dashboard->getStats() as $widget) {
            $widgets[] = $this->_getWidgetData($widget);
        }
        return $widgets;
    }

    /**
     * @param $dashboard
     *
     * @return null|Dashboard
     */
    protected function _getDashboard($dashboard) {
        if(! ($dashboard instanceof Dashboard)) {
            $dashboard = $this->em->getRepository('DeskPRO:ReportDashboard')->find((int) $dashboard);
            if (!$dashboard) throw $this->createNotFoundException('Dashboard not found!');
        }
        return $dashboard;
    }

    protected function _getDashboardData($dashboard)
    {
        if(! ($dashboard instanceof Dashboard)) {
           $dashboard = $this->_getDashboard($dashboard);
        }
        $data = array(
            'title'   => $dashboard->getTitle(),
            'id'      => $dashboard->getId(),
            'default' => $dashboard->isDefault(),
            'loaded'  => false,
            'reports' => array(),
        );
        return $data;
    }
}
