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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Application\DeskPRO\Entity\ReportDashboardWidget as ReportDashboardWidgetEntity;
use Application\DeskPRO\Entity\ReportWidget;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboardWidget as ReportDashboardWidgetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererRegistry;

/**
 * Class ReportDashboardWidgetHandler.
 */
class ReportDashboardWidgetHandler extends AbstractEntityHandler
{
    /**
     * @var DpqlCompiler
     */
    private $compiler;

    /**
     * @var ReportsRendererRegistry
     */
    private $reportsRendererRegistry;

    /**
     * Constructor.
     *
     * @param DpqlCompiler            $compiler
     * @param ReportsRendererRegistry $reportsRendererRegistry
     */
    public function __construct(DpqlCompiler $compiler, ReportsRendererRegistry $reportsRendererRegistry)
    {
        $this->compiler                = $compiler;
        $this->reportsRendererRegistry = $reportsRendererRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ReportDashboardWidgetEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportDashboardWidgetEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new ReportDashboardWidgetModel($entity);

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'rendered_result',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getRenderedResult'], [$entity, $context->getUser()]),
            $model
        );

        return $model;
    }

    /**
     * @param ReportDashboardWidgetEntity $entity
     * @param Person                      $person
     *
     * @return string
     */
    public function getRenderedResult(ReportDashboardWidgetEntity $entity, Person $person = null)
    {
        $report    = $entity->getReport();
        $dashboard = $report->getDashboard();
        $widget    = $entity->getWidget();
        $query     = $widget->getQuery();

        $variables       = [];
        $reportVariables = $report->getVariables();
        $widgetVariables = $entity->getVariables();

        foreach ($widgetVariables as $widgetVariable) {
            foreach ($reportVariables as $reportVariable) {
                if ($reportVariable['name'] === $widgetVariable['name']) {
                    $widgetVariable['value'] = $reportVariable['value'];
                }
            }

            $variables[] = $widgetVariable;
        }

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

        $query    = $this->compiler->compile($query, ['variables' => $variables]);
        $results  = $query->getResults();
        $renderer = $this->reportsRendererRegistry->getRenderer(ReportWidget::getGraphType($entity->getType()), 'json');

        if ($entity->getOptions()) {
            $options = @json_decode($entity->getOptions(), true) ?: [];
        } else {
            $options = [];
        }
        $data = $renderer->render($results, $options);
        if ($data && $entity->getType() == ReportDashboardWidgetEntity::WIDGET_TYPE_TABLE) {
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
}
