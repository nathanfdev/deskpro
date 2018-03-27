<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\ReportDashboardReport as ReportDashboardReportEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboardReport as ReportDashboardReportModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ReportDashboardReportHandler.
 */
class ReportDashboardReportHandler extends AbstractEntityHandler
{
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
        return new ReportDashboardReportModel($entity, $context->getUser());
    }
}
