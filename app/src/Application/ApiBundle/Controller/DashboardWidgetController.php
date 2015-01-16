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
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;
use Application\DeskPRO\Dpql\Statement\Display;

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
class DashboardWidgetController extends AbstractController
{

    /** @var DashboardService */
    protected $service;

    /** @var DashboardPermissionService */
    protected $permissionsService;

    /** @var  array */
    protected $widgetGraphTypesMapping = array(
        'simple_bars'  => 'BAR',
        'bars'         => 'BAR',
        'simple_lines' => 'LINE',
        'lines'        => 'LINE',
        'area'         => 'AREA',
        'simple_area'  => 'AREA',
        'pie'          => 'PIE',
    );

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
        /** @var Tab $tab */
        $tab = $this->service->getReport($id);

        $postData = $this->in->getAll('post');
        /**
         * @var \Application\DeskPRO\Reports\Builder $reports_builder
         */
        $reports_builder = $this->container->getSystemService('reports_builder');
        $report_widget = $reports_builder->getById($postData['widget_id']);
        if(!$report_widget) {
            throw $this->createNotFoundException('ReportWidget not found!');
        }

        $widget = new Widget();
        if($postData['widget_variables']) {
            $widget->setVariables($postData['widget_variables']);
        }

        $widget
            ->setTitle($postData['name'])
            ->setType($postData['type'])

            ->setSize(
                array
                (
                    $postData['sizeX'],
                    $postData['sizeY']
                )
            )
            ->setPosition(
                array(
                    $postData['row'],
                    $postData['col']
                )
            )
            ->setReport($tab)
            ->setWidget($report_widget);

        $this->em->persist($widget);
        $this->em->flush();

        return $this->createApiSuccessResponse($this->service->getWidgetData($widget));
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
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find($id);
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

    protected function _getWidgetData($widget)
    {
        if(! ($widget instanceof Widget)) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget);
        }
        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $report = $widget->getWidget();
        $query = $report->query;
        $mapped = isset($this->widgetGraphTypesMapping[$widget['type']]) ? $this->widgetGraphTypesMapping[$widget['type']] : 'TABLE';
        $query = preg_replace("#^DISPLAY.*?\n#", "DISPLAY {$mapped}\n", $query);
        $widget_data = Display::renderQuery('json', $query);
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

    public function reportsListAction()
    {
        /** @var \Application\DeskPRO\EntityRepository\ReportWidget $repository */
        $repository = $this->em->getRepository('DeskPRO:ReportWidget');
        $reports = $repository->getAllReports();
        $api_data = array(
            'reports' => array(),
            'labels' => array(),
        );
        foreach ($reports as $report) {
            $datum = $report->toApiData();
            $translatedLabels = array();
            foreach($datum['labels'] as $label) {
               $translatedLabels[] = $this->container->getTranslator()->phrase('reports.labels.'.$label);
            }
            $datum['labels'] = $translatedLabels;
            $api_data['labels'] = array_merge($api_data['labels'], $translatedLabels);
            $api_data['reports'][] = $datum;
        }
        $api_data['labels'] = array_values(array_unique($api_data['labels']));
        return $this->createApiResponse($api_data);
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
        /** @var Widget $widget */
        $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find($id);
        if($widget && $this->permissionsService->checkEditableDashboard($widget->getReport()->getDashboard())) {
            $this->em->remove($widget);
            $this->em->flush();
            return $this->createApiDeleteResponse();
        } else {
            throw $this->createNotFoundException('Widget not found!');
        }
    }


}
