<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Reports;

use Application\DeskPRO\Entity\ReportDashboard as ReportDashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as ReportDashboardReportEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Reports\ReportDashboard as ReportDashboardModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class ReportDashboardHandler.
 */
class ReportDashboardHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int[]
     */
    private $dashboardIds = [];

    /**
     * @var ReportDashboardReportEntity[]
     */
    private $reports;

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
        return ReportDashboardEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param ReportDashboardEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->dashboardIds[] = $entity->getId();

        $model     = new ReportDashboardModel($entity);
        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'reports',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getReports'], [$entity]),
            $model
        );

        return $model;
    }

    /**
     * @param ReportDashboardEntity $dashboard
     *
     * @return ReportDashboardReportEntity[]
     */
    public function getReports(ReportDashboardEntity $dashboard)
    {
        if (null === $this->reports) {
            $this->reports = $this->em->getRepository(ReportDashboardReportEntity::class)->findBy([
                'dashboard' => $this->dashboardIds,
            ]);
        }

        $dashboardReports = [];
        foreach ($this->reports as $report) {
            if ($report->getDashboard() === $dashboard) {
                $dashboardReports[] = $report;
            }
        }

        return $dashboardReports;
    }
}
