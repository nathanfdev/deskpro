<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace Application\LegacyApiBundle\Service;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;
use Doctrine\ORM\EntityManager;

/**
 * Class DashboardWidget.
 */
class DashboardWidget
{
    const OUTER_TYPE_OVERVIEW            = 'overview';
    const OUTER_TYPE_PERFORMANCE         = 'performance';
    const OUTER_TYPE_TICKET_SATISFACTION = 'ticket_satisfaction';

    const WIDGET_TYPE_HARDCODED_OVERVIEW            = 'reports_overview';
    const WIDGET_TYPE_HARDCODED_PERFORMANCE         = 'agent_performance';
    const WIDGET_TYPE_HARDCODED_TICKET_SATISFACTION = 'ticket_satisfaction';
    const WIDGET_TYPE_HARDCODED_UNDEFINED           = 'hardcoded';

    const WIDGET_RENDER_TYPE_BAR   = 'simple_bars';
    const WIDGET_RENDER_TYPE_LINE  = 'simple_lines';
    const WIDGET_RENDER_TYPE_AREA  = 'simple_area';
    const WIDGET_RENDER_TYPE_PIE   = 'pie';
    const WIDGET_RENDER_TYPE_TABLE = 'table';

    const LEGACY_RENDER_TYPE_BAR   = 'BAR';
    const LEGACY_RENDER_TYPE_LINE  = 'LINE';
    const LEGACY_RENDER_TYPE_AREA  = 'AREA';
    const LEGACY_RENDER_TYPE_PIE   = 'PIE';
    const LEGACY_RENDER_TYPE_TABLE = 'TABLE';

    const WIDGET_VALUE_FROM_REPORT = 'from_report_value';

    /**
     * @var array
     */
    protected $widgetGraphTypesMapping = [
        'simple_bars'  => self::LEGACY_RENDER_TYPE_BAR,
        'bars'         => self::LEGACY_RENDER_TYPE_BAR,
        'simple_lines' => self::LEGACY_RENDER_TYPE_LINE,
        'lines'        => self::LEGACY_RENDER_TYPE_LINE,
        'area'         => self::LEGACY_RENDER_TYPE_AREA,
        'simple_area'  => self::LEGACY_RENDER_TYPE_AREA,
        'pie'          => self::LEGACY_RENDER_TYPE_PIE,
        'table'        => self::LEGACY_RENDER_TYPE_TABLE,
    ];

    /**
     * DashboardWidget constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $widgetType
     *
     * @return mixed|string
     */
    public function getWidgetGraphType($widgetType)
    {
        return isset($this->widgetGraphTypesMapping[$widgetType]) ? $this->widgetGraphTypesMapping[$widgetType] : self::WIDGET_RENDER_TYPE_TABLE;
    }

    /**
     * @param $graphType
     *
     * @return string
     */
    public function getReversedWidgetGraphType($graphType)
    {
        $flipped = array_flip($this->widgetGraphTypesMapping);

        return isset($flipped[$graphType]) ? $flipped[$graphType] : 'table';
    }

    /**
     * @param $widget
     *
     * @return array
     */
    public function getWidgetData($widget)
    {
        if (!($widget instanceof DashboardWidgetEntity)) {
            $widget = $this->em->getRepository(DashboardWidgetEntity::class)->find((int) $widget);
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
            'type'             => $widget->getWidgetType(),
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

    /**
     * @param DashboardWidgetEntity $widget
     *
     * @return bool|string|array
     */
    public function renderWidgetQuery(DashboardWidgetEntity $widget)
    {
        $report = $widget->getWidget();
        $query  = $report->getQuery();

        $variables       = [];
        $reportVariables = $report->getVariables();
        $widgetVariables = $widget->getVariables();

        foreach ($reportVariables as $variable) {
            $variables[$variable['name']] = $variable;
            if (isset($widgetVariables[$variable['name']]) && isset($widgetVariables[$variable['name']]['value'])) {
                $variables[$variable['name']]['value'] = $widgetVariables[$variable['name']]['value'];
            }
        }

        return $this->renderQuery($query, ['variables' => $variables], $widget->getType(), 'json');
    }

    /**
     * @param string $query
     * @param array  $params
     * @param string $displayType
     * @param string $format
     *
     * @return bool|string|array
     */
    public function renderQuery($query, $params, $displayType, $format = 'json')
    {
        $mapped = $this->getWidgetGraphType($displayType);
        $query  = preg_replace("#^DISPLAY.*?\n#", "DISPLAY {$mapped}\n", $query);
        $error  = false;

        return Display::renderQuery($format, $query, $params, $error);
    }

    /**
     * @param DashboardReportEntity $report
     * @param DashboardReportEntity $reportPrototype
     */
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
