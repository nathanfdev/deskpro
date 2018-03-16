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

namespace DeskPRO\Bundle\ReportBundle\Dashboard;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;
use Application\DeskPRO\Entity\SavedDashboardWidget;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererInterface;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererRegistry;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Doctrine\ORM\EntityManager;

/**
 * Class DashboardWidget.
 */
class DashboardWidgetManager
{
    // These are different renderers
    const WIDGET_RENDER_TYPE_BAR    = 'simple_bars';
    const WIDGET_RENDER_TYPE_LINE   = 'simple_lines';
    const WIDGET_RENDER_TYPE_AREA   = 'simple_area';
    const WIDGET_RENDER_TYPE_PIE    = 'pie';
    const WIDGET_RENDER_TYPE_TABLE  = 'table';
    const WIDGET_RENDER_TYPE_STAT   = 'simple_stat';
    const WIDGET_RENDER_TYPE_GAUGE  = 'gauge';
    const WIDGET_RENDER_TYPE_BUBBLE = 'bubble';

    // These are different types
    const WIDGET_TYPE_GRAPH = 'graph';
    const WIDGET_TYPE_STAT  = 'stat';
    const WIDGET_TYPE_TABLE = 'table';

    const WIDGET_VALUE_FROM_REPORT = 'from_report_value';

    /**
     * @var array
     */
    protected $widgetTypesMapping = [
        self::WIDGET_RENDER_TYPE_BAR    => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_LINE   => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_AREA   => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_PIE    => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_GAUGE  => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_BUBBLE => self::WIDGET_TYPE_GRAPH,
        self::WIDGET_RENDER_TYPE_STAT   => self::WIDGET_TYPE_STAT,
        self::WIDGET_RENDER_TYPE_TABLE  => self::WIDGET_TYPE_TABLE,
    ];

    /**
     * @var array
     */
    protected $widgetGraphTypesMapping = [
        self::WIDGET_RENDER_TYPE_BAR    => ReportsRendererInterface::TYPE_BAR,
        self::WIDGET_RENDER_TYPE_LINE   => ReportsRendererInterface::TYPE_LINE,
        self::WIDGET_RENDER_TYPE_AREA   => ReportsRendererInterface::TYPE_AREA,
        self::WIDGET_RENDER_TYPE_PIE    => ReportsRendererInterface::TYPE_PIE,
        self::WIDGET_RENDER_TYPE_GAUGE  => ReportsRendererInterface::TYPE_GAUGE,
        self::WIDGET_RENDER_TYPE_BUBBLE => ReportsRendererInterface::TYPE_BUBBLE,
        self::WIDGET_RENDER_TYPE_STAT   => ReportsRendererInterface::TYPE_STAT,
        self::WIDGET_RENDER_TYPE_TABLE  => ReportsRendererInterface::TYPE_TABLE,
    ];

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
     * @param string $widgetType
     *
     * @return mixed|string
     */
    public function getWidgetGraphType($widgetType)
    {
        return isset($this->widgetGraphTypesMapping[$widgetType]) ? $this->widgetGraphTypesMapping[$widgetType] : self::WIDGET_RENDER_TYPE_TABLE;
    }

    /**
     * @param string $widgetType
     *
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
            $type   = $this->getWidgetGraphType($widget->getType());
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
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @throws \Exception
     *
     * @return array|bool|string
     */
    public function renderWidget(DashboardWidgetEntity $widget, Person $person = null)
    {
        // it might have no widget but js code
        // so skip it and render client side
        if (!$widget->getWidget()) {
            return;
        }

        $variables = $this->transformVariables($widget);
        $variables = $this->applyPermissionsToVariables($variables, $widget, $person);

        $data = $this->doRender(
            $widget->getWidget()->getQuery(),
            ['variables' => $variables],
            $this->getWidgetGraphType($widget->getType()),
            'json',
            $person,
            $widget->getOptions()
        );

        return $this->formatData($data, $widget->getType());
    }

    /**
     * @param mixed  $data
     * @param string $widgetType
     *
     * @return mixed
     */
    public function formatData($data, $widgetType)
    {
        if ($data && $widgetType == self::WIDGET_TYPE_TABLE) {
            $aoColumns = [];
            $columns   = [];

            foreach ($data['columns'] as $column) {
                $aoColumns[] = null;
                $columns[]   = ['title' => $column];
            }

            $data['aoColumns'] = $aoColumns;
            $data['columns']   = $columns;
        }

        if (empty($data) || $data === '') {
            $data = null;
        }

        return $data;
    }

    /**
     * @param array                 $variables
     * @param DashboardWidgetEntity $widget
     * @param Person|null           $person
     *
     * @return array
     */
    protected function applyPermissionsToVariables(
        array $variables,
        DashboardWidgetEntity $widget,
        Person $person = null
    ) {
        $dashboard = $widget->getReport()->getDashboard();

        if ($person && $dashboard->isAgent()) {
            /** @var ReportDashboardPermission $ownPermission */
            $ownPermission = $dashboard->getPermissions()->filter(function (ReportDashboardPermission $permission) use ($person) {
                return $permission->getPerson() === $person;
            })->first();

            if (!$ownPermission || !$ownPermission->isViewAll()) {
                foreach ($variables as &$variable) {
                    if ($variable['name'] === 'agent') {
                        $variable['field_value'] = $person->getId();
                    }
                    if ($variable['name'] === 'agent_team') {
                        $variable['field_value'] = $person->getPrimaryTeam() ? $person->getPrimaryTeam()->getId() : null;
                    }
                }
            }
        }

        return $variables;
    }

    /**
     * @param DashboardWidgetEntity $widget
     *
     * @return array
     */
    protected function transformVariables(DashboardWidgetEntity $widget)
    {
        $report          = $widget->getReport();
        $variables       = [];
        $reportVariables = $report->getVariables();
        $widgetVariables = $widget->getVariables();

        foreach ($widgetVariables as $widgetVariable) {
            foreach ($reportVariables as $reportVariable) {
                if ($reportVariable['name'] === $widgetVariable['name']) {
                    $widgetVariable['value'] = $reportVariable['value'];
                }
            }

            $variables[] = $widgetVariable;
        }

        return $variables;
    }

    /**
     * @param string $query
     * @param array  $params
     * @param Person $person
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     *
     * @return SelectPart[]
     */
    public function getCompiledQueries($query, array $params = [], Person $person = null)
    {
        $queries         = preg_split('#LAYER WITH#', $query);
        $compiledQueries = [];
        foreach ($queries as $layeredQuery) {
            $compiledQueries[] = $this->compiler->compile($layeredQuery, $params, new DpqlContext($person));
        }

        return $compiledQueries;
    }

    /**
     * @param string $query,
     * @param array  $params
     * @param string $graphType
     * @param string $format
     * @param Person $person
     * @param string $options
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @throws \Exception
     *
     * @return array|bool|string
     */
    public function doRender(
        $query,
        $params,
        $graphType,
        $format = 'json',
        Person $person = null,
        $options = ''
    ) {
        $renderer = $this->rendererRegistry->getRenderer($graphType, $format);

        $results         = [];
        $compiledQueries = $this->getCompiledQueries($query, $params, $person);
        $multiLayer      = count($compiledQueries) > 1;

        foreach ($compiledQueries as $compiledQuery) {
            $queryResult = $compiledQuery->getResults();
            if ($multiLayer) {
                $queryResult->getMetadata()->addFlag(ResultMetadata::FLAG_LAYERED);
            }
            $results[] = $queryResult;
        }

        if ($options) {
            $options = @json_decode($options, true) ?: [];
        } else {
            $options = [];
        }

        $renderedResults = [];
        foreach ($results as $queryResult) {
            $renderedResults[] = $renderer->render($queryResult, $options);
        }
        $renderedResults = array_filter($renderedResults, function ($item) {
            return $item;
        });
        if (count($renderedResults) > 1) {
            return $renderer->mergeResults($renderedResults);
        }

        return reset($renderedResults);
    }
}
