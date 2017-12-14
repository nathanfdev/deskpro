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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;
use Application\LegacyApiBundle\Service\Dashboard as DashboardService;
use Application\LegacyApiBundle\Service\DashboardPermissions as DashboardPermissionService;
use Application\LegacyApiBundle\Service\DashboardWidget;
use Application\LegacyApiBundle\Service\DashboardWidget as DashboardWidgetService;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class DashboardWidgetController extends AbstractController
{
    /** @var DashboardService */
    protected $service;

    /** @var DashboardPermissionService */
    protected $permissionsService;

    /** @var DashboardWidgetService */
    protected $widgetService;

    /**
     * {@inherited}.
     */
    public function init()
    {
        parent::init();
        $this->service            = $this->get('dashboard.service');
        $this->permissionsService = $this->get('dashboard.permissions.service');
        $this->widgetService      = $this->get('dashboard.widget.service');
    }

    /**
     * @param $id integer
     *
     * @return Response
     */
    public function addWidgetAction($id)
    {
        /** @var Tab $tab */
        $tab = $this->service->getReport($id);

        if (!$this->permissionsService->isEditableDashboard($tab->getDashboard())) {
            throw $this->createAccessDeniedException('You cant edit this dashboard!');
        }

        $postData = $this->in->getAll('post');

        /** @var \Application\DeskPRO\Reports\ReportsWidgetService $reportsWidgetService */
        $reportsWidgetService = $this->container->get('reports.widget.service');
        $reportWidget         = $reportsWidgetService->getById($postData['widget_id']);
        if (!$reportWidget) {
            throw $this->createNotFoundException('ReportWidget not found!');
        }
        $widget = new Widget();
        $widget
            ->setTitle($postData['title'])
            ->setType($postData['type'])
            ->setSize([$postData['sizeX'], $postData['sizeY']])
            ->setPosition([$postData['row'], $postData['col']])
            ->setReport($tab)
            ->setWidget($reportWidget);
        if (isset($postData['variables'])) {
            $widget->setVariables($postData['variables']);
        }
        $this->em->persist($widget);
        $this->em->flush();

        return $this->createApiSuccessResponse($this->widgetService->getWidgetData($widget));
    }

    /**
     * @param $id integer
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function saveWidgetAction($id)
    {
        if ($id) {
            $widget = $this->em->getRepository(Widget::class)->find($id);
            if (!$widget) {
                throw $this->createNotFoundException('Widget not found!');
            }
        } else {
            $widget = new Widget();
        }
        list($sizeX, $sizeY) =
            [
                $this->in->getCleanValue('size_x', 'int'),
                $this->in->getCleanValue('size_y', 'int'),
            ];
        list($row, $col) =
            [
                $this->in->getCleanValue('row', 'int'),
                $this->in->getCleanValue('col', 'int'),
            ];
        $widget->setSize([$sizeX, $sizeY])->setPosition([$row, $col]);
        if ($this->permissionsService->isEditableDashboard($widget->getReport()->getDashboard())) {
            $title = $this->in->getCleanValue('title', 'string');
            $widget->setTitle($title);
        }
        $this->em->persist($widget);
        $this->em->flush();

        return $this->createApiSuccessResponse();
    }

    /**
     * @param $widget
     *
     * @return Response
     */
    public function getWidgetDataAction($widget)
    {
        $widgetData = $this->_getWidgetData($widget);

        return $this->createApiResponse($widgetData);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function getWidgetAction($id)
    {
        return $this->createApiResponse($this->_getWidgetData($id));
    }

    /**
     * @param $widget
     *
     * @return array
     */
    protected function _getWidgetData($widget)
    {
        if (!$widget instanceof Widget && !$widget = $this->em->getRepository(Widget::class)->find((int) $widget)) {
            throw $this->createNotFoundException('Widget not found!');
        }
        if (!$widget->getWidget()) {
            throw $this->createNotFoundException('Widget not found!');
        }
        $pos             = $widget->getPosition();
        $size            = $widget->getSize();
        $reportLevelVars = $widget->getReport()->getVariables();
        $widgetVars      = $widget->getVariables() ?: [];
        foreach ($widgetVars as $key => &$var) {
            if ($var['value'] === DashboardWidget::WIDGET_VALUE_FROM_REPORT
                 && isset($reportLevelVars[$key]) && $reportLevelVars[$key] && $reportLevelVars[$key]['value']) {
                $var['value'] = $reportLevelVars[$key]['value'];
            }
        }
        $widget->setVariables($widgetVars);
        $realData = $this->widgetService->renderWidgetQuery($widget);
        if ($realData && $widget->getType() == DashboardWidgetService::WIDGET_TYPE_TABLE) {
            $aoColumns = [];
            $columns   = [];
            foreach ($realData['columns'] as $column) {
                $aoColumns[] = null;
                $columns[]   = ['title' => $column];
            }
            $realData['aoColumns'] = $aoColumns;
            $realData['columns']   = $columns;
        }

        $data = [
            'id'    => $widget->getId(),
            'title' => $widget->getTitle(),
            'row'   => $pos[0],
            'col'   => $pos[1],
            'sizeX' => $size[0],
            'sizeY' => $size[1],
            'type'  => $this->widgetService->getWidgetType($widget->getType()),
            'data'  => $realData ?: [],
        ];

        return $data;
    }

    /**
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function deleteWidgetAction($id)
    {
        /** @var Widget $widget */
        $widget = $this->em->getRepository(Widget::class)->find($id);
        if ($widget && $this->permissionsService->isEditableDashboard($widget->getReport()->getDashboard())) {
            $this->em->remove($widget);
            $this->em->flush();

            return $this->createApiDeleteResponse();
        } else {
            throw $this->createAccessDeniedException('You can\'t edit this dashboard!');
        }
    }
}
