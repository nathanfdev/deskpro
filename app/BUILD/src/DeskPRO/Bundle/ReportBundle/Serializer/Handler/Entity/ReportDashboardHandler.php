<?php

namespace DeskPRO\Bundle\ReportBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard as ReportDashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as ReportDashboardReportEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\ReportBundle\Serializer\Model\ReportDashboard as ReportDashboardModel;
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
     * @var Person[]
     */
    private $allReportsAdmins;

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

        $model     = new ReportDashboardModel($entity, $this->getAllReportAdmins());
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

    /**
     * @return Person[]
     */
    private function getAllReportAdmins()
    {
        if (!$this->allReportsAdmins) {
            $this->allReportsAdmins = $this->em
                ->getRepository(Person::class)
                ->createQueryBuilder('p')
                ->andWhere('p.can_admin = 1')
                ->orWhere('p.can_reports = 1')
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->allReportsAdmins;
    }
}
