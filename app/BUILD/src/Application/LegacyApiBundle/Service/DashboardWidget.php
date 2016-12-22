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
 * Created by PhpStorm.
 * User: Den
 * Date: 16.01.2015
 * Time: 3:24.
 */

namespace Application\LegacyApiBundle\Service;

use Application\DeskPRO\DataSync\ReportWidget;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;
use Doctrine\ORM\EntityManager;

class DashboardWidget
{
    const OUTER_TYPE_OVERVIEW            = 'overview';
    const OUTER_TYPE_PERFORMANCE         = 'performance';
    const OUTER_TYPE_TICKET_SATISFACTION = 'ticket_satisfaction';

    const WIDGET_TYPE_HARDCODED_OVERVIEW            = 'reports_overview';
    const WIDGET_TYPE_HARDCODED_PERFORMANCE         = 'agent_performance';
    const WIDGET_TYPE_HARDCODED_TICKET_SATISFACTION = 'ticket_satisfaction';
    const WIDGET_TYPE_HARDCODED_UNDEFINED           = 'hardcoded';

    const WIDGET_RENDER_TYPE_BAR   = 'BAR';
    const WIDGET_RENDER_TYPE_LINE  = 'LINE';
    const WIDGET_RENDER_TYPE_AREA  = 'AREA';
    const WIDGET_RENDER_TYPE_PIE   = 'PIE';
    const WIDGET_RENDER_TYPE_TABLE = 'TABLE';

    const WIDGET_TYPE_GRAPH = 'graph';
    const WIDGET_TYPE_TABLE = 'table';
    const WIDGET_TYPE_STAT  = 'stat';

    protected $widgetGraphTypesMapping = [
        'simple_bars'  => self::WIDGET_RENDER_TYPE_BAR,
        'bars'         => self::WIDGET_RENDER_TYPE_BAR,
        'simple_lines' => self::WIDGET_RENDER_TYPE_LINE,
        'lines'        => self::WIDGET_RENDER_TYPE_LINE,
        'area'         => self::WIDGET_RENDER_TYPE_AREA,
        'simple_area'  => self::WIDGET_RENDER_TYPE_AREA,
        'pie'          => self::WIDGET_RENDER_TYPE_PIE,
        'table'        => self::WIDGET_RENDER_TYPE_TABLE,
    ];

    protected $widgetTypesMapping = [
        'simple_bars'  => self::WIDGET_TYPE_GRAPH,
        'bars'         => self::WIDGET_TYPE_GRAPH,
        'simple_lines' => self::WIDGET_TYPE_GRAPH,
        'lines'        => self::WIDGET_TYPE_GRAPH,
        'area'         => self::WIDGET_TYPE_GRAPH,
        'simple_area'  => self::WIDGET_TYPE_GRAPH,
        'pie'          => self::WIDGET_TYPE_GRAPH,
        'table'        => self::WIDGET_TYPE_TABLE,
        'simple_stat'  => self::WIDGET_TYPE_STAT,
    ];

    public function getWidgetType($widgetType)
    {
        return isset($this->widgetTypesMapping[$widgetType]) ? $this->widgetTypesMapping[$widgetType] : self::WIDGET_TYPE_TABLE;
    }

    public function getWidgetGraphType($widgetType)
    {
        return isset($this->widgetGraphTypesMapping[$widgetType]) ? $this->widgetGraphTypesMapping[$widgetType] : self::WIDGET_RENDER_TYPE_TABLE;
    }

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function changeDisplayType(ReportWidget $reportWidget, DashboardWidgetEntity $dashboardWidget)
    {
    }

    public function getWidgetData($widget)
    {
        if (!($widget instanceof DashboardWidgetEntity)) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget);
        }
        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $data = [
            'id'               => $widget->getId(),
            'title'            => $widget->getTitle(),
            'row'              => $pos[0],
            'col'              => $pos[1],
            'sizeX'            => $size[0],
            'sizeY'            => $size[1],
            'widget_id'        => $widget->getReport() ? $widget->getReport()->getId() : 0,
            'widget_variables' => $widget->getVariables(),
            'type'             => $this->getWidgetType($widget->getType()),
            'data'             => [],
        ];
        if ($hc_data = $widget->getHcData()) {
            switch ($hc_data['outer_type']) {
                case self::OUTER_TYPE_OVERVIEW:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_OVERVIEW;
                    break;
                case self::OUTER_TYPE_PERFORMANCE:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_PERFORMANCE;
                    break;
                case self::OUTER_TYPE_TICKET_SATISFACTION:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_TICKET_SATISFACTION;
                    break;
                default:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_UNDEFINED;
            }
            $data['inner_type'] = $hc_data['inner_type'];
            $data['outer_type'] = $hc_data['outer_type'];
        }

        return $data;
    }

    public function renderWidgetQuery(DashboardWidgetEntity $widget)
    {
        $report    = $widget->getWidget();
        $query     = $report->getQuery();
        $mapped    = $this->getWidgetGraphType($widget->getType());
        $query     = preg_replace("#^DISPLAY.*?\n#", "DISPLAY {$mapped}\n", $query);
        $error     = false;
        $variables = $widget->getVariables();

        return Display::renderQuery('json', $query, $variables, $error);
    }

    public function copyWidgetLinks(DashboardReportEntity $report, DashboardReportEntity $reportPrototype)
    {
        foreach ($reportPrototype->getWidgets() as $widget_prototype) {
            $widget = new DashboardWidgetEntity();
            $widget
                ->setTitle($widget_prototype->getTitle())
                ->setPosition($widget_prototype->getPosition())
                ->setSize($widget_prototype->getSize())
                ->setType($widget_prototype->getType())
                ->setVariables($widget_prototype->getVariables())
                ->setReport($report)
                ->setWidget($widget_prototype->getWidget());
            $this->em->persist($widget);
            $report->addWidget($widget);
        }
    }
}
