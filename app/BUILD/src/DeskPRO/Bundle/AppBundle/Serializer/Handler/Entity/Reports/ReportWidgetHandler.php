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

use Application\DeskPRO\Entity\ReportWidget as ReportWidgetEntity;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportWidget as ReportWidgetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererRegistry;
use DeskPRO\Bundle\ReportBundle\Service\DashboardWidget;

/**
 * Class ReportWidgetHandler.
 */
class ReportWidgetHandler extends AbstractEntityHandler
{
    /**
     * @var DpqlCompiler
     */
    private $compiler;

    /**
     * @var ReportsRendererRegistry
     */
    private $rendererRegistry;

    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var DashboardWidget
     */
    private $dashboardWidgetService;

    /**
     * Constructor.
     *
     * @param DpqlCompiler            $compiler
     * @param ReportsRendererRegistry $rendererRegistry
     * @param Translate               $translate
     * @param DashboardWidget         $dashboardWidgetService
     */
    public function __construct(
        DpqlCompiler $compiler,
        ReportsRendererRegistry $rendererRegistry,
        Translate $translate,
        DashboardWidget $dashboardWidgetService
    ) {
        $this->compiler               = $compiler;
        $this->rendererRegistry       = $rendererRegistry;
        $this->translate              = $translate;
        $this->dashboardWidgetService = $dashboardWidgetService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ReportWidgetEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportWidgetEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $translated = [];
        foreach ($entity->getLabels() as $label) {
            $phraseName   = 'reports.labels.'.strtolower($label);
            $translated[] = $this->translate->hasPhrase($phraseName) ? $this->translate->phrase($phraseName) : ucfirst($label);
        }

        $statement  = $this->compiler->compile($entity->getQuery());
        $queryParts = $statement->getDpqlPartsForInput();

        $model = new ReportWidgetModel($entity, $queryParts, $translated);

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'rendered_result',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getRenderedResult'], [$entity]),
            $model
        );

        return $model;
    }

    /**
     * @param ReportWidgetEntity $entity
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @throws \Exception
     *
     * @return array
     */
    public function getRenderedResult(ReportWidgetEntity $entity)
    {
        $result = [];
        foreach ($entity->getDisplayTypes() as $displayType) {
            $graphType = $this->dashboardWidgetService->getWidgetGraphType($displayType);
            $data      = $this->dashboardWidgetService->doRender(
                $entity->getQuery(),
                ['variables' => $entity->getVariables()],
                $graphType
            );

            $data              = $this->dashboardWidgetService->formatData($data, $displayType);
            $data['chartType'] = $graphType;
            $result[]          = $data;
        }

        return $result;
    }
}
