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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;
use Application\DeskPRO\Entity\SavedDashboardWidget;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererRegistry;
use Doctrine\ORM\EntityManager;

/**
 * Class DashboardWidget.
 */
class DashboardWidget
{
    const WIDGET_RENDER_TYPE_BAR   = 'simple_bars';
    const WIDGET_RENDER_TYPE_LINE  = 'simple_lines';
    const WIDGET_RENDER_TYPE_AREA  = 'simple_area';
    const WIDGET_RENDER_TYPE_PIE   = 'pie';
    const WIDGET_RENDER_TYPE_TABLE = 'table';
    const WIDGET_RENDER_TYPE_STAT  = 'simple_stat';

    const LEGACY_RENDER_TYPE_BAR   = 'BAR';
    const LEGACY_RENDER_TYPE_LINE  = 'LINE';
    const LEGACY_RENDER_TYPE_AREA  = 'AREA';
    const LEGACY_RENDER_TYPE_PIE   = 'PIE';
    const LEGACY_RENDER_TYPE_TABLE = 'TABLE';
    const LEGACY_RENDER_TYPE_STAT  = 'STAT';

    const WIDGET_TYPE_GRAPH     = 'graph';
    const WIDGET_TYPE_STAT      = 'stat';
    const WIDGET_TYPE_HARDCODED = 'hardcoded';
    const WIDGET_TYPE_BAR       = 'bar';
    const WIDGET_TYPE_PIE       = 'pie';
    const WIDGET_TYPE_TABLE     = 'table';

    /**
     * @var array
     */
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

    const WIDGET_VALUE_FROM_REPORT = 'from_report_value';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DpqlCompiler
     */
    private $compiler;

    /**
     * @var ReportsRendererRegistry
     */
    private $rendererRegistry;

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
        'simple_stat'  => self::LEGACY_RENDER_TYPE_STAT,
    ];

    /**
     * DashboardWidget constructor.
     *
     * @param EntityManager           $em
     * @param DpqlCompiler            $compiler
     * @param ReportsRendererRegistry $rendererRegistry
     */
    public function __construct(EntityManager $em, DpqlCompiler $compiler, ReportsRendererRegistry $rendererRegistry)
    {
        $this->em               = $em;
        $this->compiler         = $compiler;
        $this->rendererRegistry = $rendererRegistry;
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
     * @return string
     */
    public function getWidgetType($widgetType)
    {
        return isset($this->widgetTypesMapping[$widgetType]) ? $this->widgetTypesMapping[$widgetType] : self::WIDGET_TYPE_TABLE;
    }

    /**
     * @param $widget
     *
     * @return array
     */
    public function getWidgetData($widget)
    {
        if (!$widget instanceof DashboardWidgetEntity && !$widget instanceof SavedDashboardWidget) {
            $widget = $this->em->getRepository(DashboardWidgetEntity::class)->find((int) $widget);
        }

        $type = '';
        if ($widget instanceof DashboardWidgetEntity) {
            $report = $widget->getReport();
            $type   = $widget->getWidgetType();
        } elseif ($widget instanceof SavedDashboardWidget) {
            $report = $widget->getSavedReport();
        } else {
            $report = null;
        }

        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $data = [
            'id'               => $widget->getId(),
            'title'            => $widget->getTitle(),
            'row'              => $pos[0] + 1,
            'col'              => $pos[1],
            'sizeX'            => $size[0],
            'sizeY'            => $size[1],
            'widget_id'        => $report ? $report->getId() : 0,
            'widget_variables' => $widget->getVariables(),
            'options'          => $widget->getOptions(),
            'type'             => $type,
            'data'             => [],
        ];

        return $data;
    }

    /**
     * @param DashboardWidgetEntity $widget
     * @param Person|null           $person
     *
     * @return array|bool|string
     */
    public function renderWidgetQuery(DashboardWidgetEntity $widget, Person $person = null)
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

        return $this->renderQuery($query, ['variables' => $variables], $widget->getType(), 'json', $person);
    }

    /**
     * @param string $query
     * @param array  $params
     * @param string $displayType
     * @param string $format
     * @param Person $person
     *
     * @return array|bool|string
     */
    public function renderQuery($query, $params, $displayType, $format = 'json', Person $person = null)
    {
        $mapped   = $this->getWidgetGraphType($displayType);
        $query    = $this->compiler->compile($query, $params, new DpqlContext($person));
        $renderer = $this->rendererRegistry->getRenderer($mapped, $format);

        return $renderer->render($query->getResults());
    }
}
