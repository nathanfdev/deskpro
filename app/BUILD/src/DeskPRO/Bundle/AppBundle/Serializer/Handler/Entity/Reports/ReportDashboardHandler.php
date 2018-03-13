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
