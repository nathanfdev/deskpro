<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\ReportDashboardWidget as ReportDashboardWidgetEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboardWidget as ReportDashboardWidgetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;

/**
 * Class ReportDashboardWidgetHandler.
 */
class ReportDashboardWidgetHandler extends AbstractEntityHandler
{
    /**
     * @var DashboardWidgetManager
     */
    private $dashboardWidgetService;

    /**
     * Constructor.
     *
     * @param DashboardWidgetManager $dashboardWidgetService
     */
    public function __construct(DashboardWidgetManager $dashboardWidgetService)
    {
        $this->dashboardWidgetService = $dashboardWidgetService;
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
        $model->setWidgetType($this->dashboardWidgetService->getWidgetType($entity->getType()));

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'rendered_result',
            $entity->getId(),
            new CallbackDeferredProperty([$this->dashboardWidgetService, 'renderWidget'], [$entity, $context->getUser()]),
            $model
        );

        return $model;
    }
}
