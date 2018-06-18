<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\ReportWidget as ReportWidgetEntity;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportWidget as ReportWidgetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\ReportsRendererRegistry;
use DeskPRO\Bundle\ReportBundle\Reports\SplitResults;

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
     * @var DashboardWidgetManager
     */
    private $dashboardWidgetService;

    /**
     * Constructor.
     *
     * @param DpqlCompiler            $compiler
     * @param ReportsRendererRegistry $rendererRegistry
     * @param Translate               $translate
     * @param DashboardWidgetManager  $dashboardWidgetService
     */
    public function __construct(
        DpqlCompiler $compiler,
        ReportsRendererRegistry $rendererRegistry,
        Translate $translate,
        DashboardWidgetManager $dashboardWidgetService
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
     *
     * @throws \Exception
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $translated = [];
        foreach ($entity->getLabels() as $label) {
            $phraseName   = 'reports.labels.'.strtolower($label);
            $translated[] = $this->translate->hasPhrase($phraseName) ? $this->translate->phrase($phraseName) : ucfirst($label);
        }

        $extendedQuery = false;
        try {
            $statement  = $this->compiler->compile($entity->getQuery());
            $queryParts = $statement->getDpqlPartsForInput();
        } catch (DpqlException $e) {
            if ($e->getCode() === DpqlException::CODE_LAYERED_DIRECT_COMPILE_ERROR) {
                $extendedQuery = true;
                $queryParts    = [];
            } else {
                throw $e;
            }
        }

        $model = new ReportWidgetModel($entity, $queryParts, $translated, $extendedQuery);

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
     * @throws \Exception
     *
     * @return array
     */
    public function getRenderedResult(ReportWidgetEntity $entity)
    {
        try {
            $result = [];
            foreach ($entity->getDisplayTypes() as $displayType) {
                $graphType = $this->dashboardWidgetService->getWidgetGraphType($displayType);
                $data      = $this->dashboardWidgetService->doRender(
                    $entity->getQuery(),
                    ['variables' => $entity->getVariables()],
                    $graphType
                );

                if ($data instanceof SplitResults) {
                    foreach ($data->getResults() as $splitResult) {
                        $data = $this->dashboardWidgetService->formatData($splitResult->getResults(), $displayType);
                        if ($data) {
                            $data['title']     = $splitResult->getTitle();
                            $data['chartType'] = $graphType;
                        }
                        $result[] = $data;
                    }
                } else {
                    $data = $this->dashboardWidgetService->formatData($data, $displayType);
                    if ($data) {
                        $data['chartType'] = $graphType;
                    }
                    $result[] = $data;
                }
            }

            return $result;
        } catch (\Exception $e) {
            return;
        }
    }
}
