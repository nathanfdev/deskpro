<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ReportDashboardReport as ReportDashboardReportEntity;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Serializer\Model\ReportDashboardReport as ReportDashboardReportModel;
use Doctrine\ORM\EntityManager;

/**
 * Class ReportDashboardReportHandler.
 */
class ReportDashboardReportHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int[]
     */
    private $reportIds = [];

    /**
     * @var array
     */
    private $widgets;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return ReportDashboardReportEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportDashboardReportEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->reportIds[] = $entity->getId();

        $model = new ReportDashboardReportModel($entity, $context->getUser());
        $context->getSideloadStore()->addCustomSideload(
            'widgets',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getWidgets'], [$entity]),
            $model
        );

        return $model;
    }

    /**
     * @param ReportDashboardReportEntity $entity
     *
     * @return array
     */
    public function getWidgets(ReportDashboardReportEntity $entity)
    {
        if (null === $this->widgets) {
            $this->widgets = $this->em->getRepository(ReportDashboardWidget::class)->findBy([
                'report' => $this->reportIds,
            ]);
        }

        $dashboardWidgets = [];
        foreach ($this->widgets as $widget) {
            if ($widget->getReport() === $entity) {
                $dashboardWidgets[] = $widget;
            }
        }

        return $dashboardWidgets;
    }
}
