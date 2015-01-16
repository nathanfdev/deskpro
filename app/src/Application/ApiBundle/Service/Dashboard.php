<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30
 */

namespace Application\ApiBundle\Service;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;

/**
 * Class Dashboard
 * @package Application\ApiBundle\Service
 */
class Dashboard
{
    /**
     * @param EntityManager   $em
     * @param DashboardWidget $widgetService
     */
    public function __construct(EntityManager $em, DashboardWidget $widgetService)
    {
        $this->em = $em;
        $this->widgetService = $widgetService;
    }

    ///
    /// DASHBOARD SECTION
    ///

    /**
     * @param $dashboard
     *
     * @return null|DashboardEntity
     */
    public function getDashboard($dashboard) {
        if(! ($dashboard instanceof DashboardEntity)) {
            $dashboard = $this->em->getRepository('DeskPRO:ReportDashboard')->find((int) $dashboard);
            if (!$dashboard) throw new NotFoundHttpException('Dashboard not found!');
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
        if(! ($dashboard instanceof DashboardEntity)) {
            $dashboard = $this->getDashboard($dashboard);
        }

        $data = array(
            'title'   => $dashboard->getTitle(),
            'id'      => $dashboard->getId(),
            'default' => $dashboard->isDefault(),
            'loaded'  => false,
            'reports' => array(),
        );
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
     * @return array
     */
    public function getReportData($report)
    {
        $report = $this->getReport($report);
        $widgets = array();
        foreach($report->getWidgets() as $widget) {

            $wdata = $this->widgetService->getWidgetData($widget);
            $widgets[] = $wdata;
        }
        $data = array(
            'title'        => $report->getTitle(),
            'id'           => $report->getId(),
            'dashboard_id' => $report->getDashboard()->getId(),
            'loaded'       => true,
            'options'      => array(
                'columns'  => $report->getColumns(),
                "floating" => false,
                "swapping" => false,
            ),
            'widgets'      => $widgets,
        );
        return $data;
    }

    /**
     * @param $dashboard
     * @return array
     */
    public function getReportsData($dashboard)
    {
        $dashboard = $this->getDashboard($dashboard);

        $reports_data = array();
        foreach ($dashboard->getReports() as $report)
        {

            $data = array(
                'title'        => $report->getTitle(),
                'id'           => $report->getId(),
                'dashboard_id' => $dashboard->getId(),
                'loaded'       => false,
                'deleted'      => false,
                'options'      => array(
                    'columns'  => $report->getColumns(),
                    "floating" => false,
                    "swapping" => false,
                ),
                'widgets'      => array()
            );
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
        if(! ($report instanceof DashboardReportEntity)) {
            $report_id = $report;
            $report = $this->em->getRepository('DeskPRO:ReportDashboardReport')->find((int) $report_id);
            if (!$report) throw new NotFoundHttpException(sprintf('DashboardReport[%d] not found!', $report_id));
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
        if($flush) {
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
        if($flush) {
            $this->em->flush();
        }
        return $this->getReportData($report);
    }


    /**
     * @param $dashboard
     *
     * @return int
     */
    public function getLastSortOrder($dashboard) {
        $dashboard = $this->getDashboard($dashboard);
        /** @var ArrayCollection $allReports */
        $allReports = $dashboard->getReports();
        return $allReports->last() ? $allReports->last()->getSortOrder() + 1 : 1;
    }


}