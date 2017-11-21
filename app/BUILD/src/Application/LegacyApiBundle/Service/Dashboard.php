<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30.
 */

namespace Application\LegacyApiBundle\Service;

use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Dashboard.
 */
class Dashboard
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DashboardWidget
     */
    private $widgetService;

    /**
     * @var array
     */
    private $labelsStorage;

    /**
     * @param EntityManager   $em
     * @param DashboardWidget $widgetService
     * @param Translate       $translator
     */
    public function __construct(EntityManager $em, DashboardWidget $widgetService, Translate $translator)
    {
        $this->em            = $em;
        $this->widgetService = $widgetService;

        $repository = $this->em->getRepository(ReportWidget::class);
        foreach ($repository->findBy(['is_custom' => false]) as $reportWidget) {
            foreach ($reportWidget->getLabels() as $label) {
                $phraseName = 'reports.labels.'.strtolower($label);
                //sic! We need to have reverted key-value here
                $key                       = $translator->hasPhrase($phraseName) ? $translator->phrase($phraseName) : ucfirst($label);
                $value                     = strtolower($label);
                $this->labelsStorage[$key] = $value;
            }
        }
    }

    ///
    /// DASHBOARD SECTION
    ///

    /**
     * @param $dashboard
     *
     * @return null|DashboardEntity
     */
    public function getDashboard($dashboard)
    {
        if (!($dashboard instanceof DashboardEntity)) {
            $dashboard = $this->em->getRepository(DashboardEntity::class)->find((int) $dashboard);
            if (!$dashboard) {
                throw new NotFoundHttpException('Dashboard not found!');
            }
        }

        return $dashboard;
    }

    /**
     * @param $dashboard
     *
     * @return array
     */
    public function getDashboardData($dashboard)
    {
        if (!($dashboard instanceof DashboardEntity)) {
            $dashboard = $this->getDashboard($dashboard);
        }

        $data = [
            'id'         => $dashboard->getId(),
            'title'      => $dashboard->getTitle(),
            'is_default' => $dashboard->isDefault(),
            'reports'    => array_map(
                function (DashboardReportEntity $r) {
                    return ['id' => $r->getId(), 'title' => $r->getTitle()];
                },
                $dashboard->getReports()->toArray() ?: []
            ),
        ];

        return $data;
    }

    /**
     * @param DashboardEntity $dashboard
     */
    public function deleteDashboard(DashboardEntity $dashboard)
    {
        $this->em->remove($dashboard);
        $this->em->flush();
    }

    /**
     * @param DashboardEntity $dashboard
     *
     * @return array
     */
    public function saveDashboard(DashboardEntity $dashboard)
    {
        $this->em->persist($dashboard);
        $this->em->flush();

        return $this->getDashboardData($dashboard);
    }

    ///
    /// REPORTS SECTION
    ///

    /**
     * @param $report
     *
     * @return array
     */
    public function getReportData($report)
    {
        $report  = $this->getReport($report);
        $widgets = [];
        foreach ($report->getWidgets() as $widget) {
            $wdata     = $this->widgetService->getWidgetData($widget);
            $widgets[] = $wdata;
        }
        $data = [
            'title'        => $report->getTitle(),
            'id'           => $report->getId(),
            'dashboard_id' => $report->getDashboard()->getId(),
            'loaded'       => true,
            'options'      => [
                'columns'  => $report->getColumns(),
                'floating' => false,
                'swapping' => false,
            ],
            'widgets'   => $widgets,
            'variables' => $report->getVariables(),
        ];

        return $data;
    }

    /**
     * @param $dashboard
     *
     * @return array
     */
    public function getReportsData($dashboard)
    {
        $dashboard = $this->getDashboard($dashboard);

        $reports_data = [];
        foreach ($dashboard->getReports() as $report) {
            $data = [
                'title'        => $report->getTitle(),
                'id'           => $report->getId(),
                'dashboard_id' => $dashboard->getId(),
                'loaded'       => false,
                'deleted'      => false,
                'options'      => [
                    'columns'  => $report->getColumns(),
                    'floating' => false,
                    'swapping' => false,
                ],
                'widgets' => [],
            ];
            $reports_data[] = $data;
        }

        return $reports_data;
    }

    /**
     * @param $report
     *
     * @return null|DashboardReportEntity
     */
    public function getReport($report)
    {
        if (!($report instanceof DashboardReportEntity)) {
            $report_id = $report;
            $report    = $this->em->getRepository(DashboardReportEntity::class)->find((int) $report_id);
            if (!$report) {
                throw new NotFoundHttpException(sprintf('DashboardReport[%d] not found!', $report_id));
            }
        }

        return $report;
    }

    /**
     * @param      $report
     * @param bool $flush
     */
    public function deleteReport($report, $flush = false)
    {
        $report = $this->getReport($report);
        $this->em->remove($report);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * @param DashboardReportEntity $report
     * @param bool                  $flush
     *
     * @return array
     */
    public function saveReport(DashboardReportEntity $report, $flush = false)
    {
        $this->em->persist($report);
        if ($flush) {
            $this->em->flush();
        }

        return $this->getReportData($report);
    }

    /**
     * @param $dashboard
     *
     * @return int
     */
    public function getLastSortOrder($dashboard)
    {
        $dashboard = $this->getDashboard($dashboard);
        /** @var ArrayCollection $allReports */
        $allReports = $dashboard->getReports();

        return $allReports->last() ? $allReports->last()->getSortOrder() + 1 : 1;
    }

    /**
     * @param string $label
     *
     * @return string
     */
    public function mapLabelToSystemName($label)
    {
        return isset($this->labelsStorage[$label]) ? $this->labelsStorage[$label] : strtolower($label);
    }
}
