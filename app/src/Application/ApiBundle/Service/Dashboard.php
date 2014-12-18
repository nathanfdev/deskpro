<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30
 */

namespace Application\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use \Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Application\DeskPRO\Dpql\Statement\Display;

use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;



class Dashboard
{
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

    public function deleteDashboard(DashboardEntity $dashboard)
    {
        $this->em->remove($dashboard);
        $this->em->flush();
    }

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
     * @param $dashboard
     * @return array
     */
    public function getReportsData($dashboard)
    {
        $dashboard = $this->getDashboard($dashboard);

        $reports_data = array();
        foreach ($dashboard->getReports() as $report)
        {
//            $widgets = array();
//            foreach($report->getWidgets() as $widget) {
//                $pos  = $widget->getPosition();
//                $size = $widget->getSize();
//                $widgets[] = array(
//                    'id'    => $widget->getId(),
//                    'name'  => $widget->getTitle(),
//                    "row"   => $pos[0],
//                    "col"   => $pos[1],
//                    "sizeX" => $size[0],
//                    "sizeY" => $size[1],
//                    "type"  => "graph",
//                );
//            }
            $data = array(
                'title'        => $report->getTitle(),
                'id'           => $report->getId(),
                'dashboard_id' => $dashboard->getId(),
                'loaded'       => false,
                'options'      => array(
                    'columns'  => $report->getColumns(),
                    "floating" => false,
                    "swapping" => false,
                ),
                //                'widgets'  => $widgets,
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
            $report = $this->em->getRepository('DeskPRO:ReportDashboardReport')->find((int) $report);
            if (!$report) throw new NotFoundHttpException('DashboardReport not found!');
        }
        return $report;
    }

    /**
     * @param DashboardReportEntity $report
     * @return array
     */
    public function saveReport(DashboardReportEntity $report)
    {
        $this->em->persist($report);
        $this->em->flush();
        return $this->getReportData($report);
    }

    /**
     * @param $report
     * @return array
     */
    public function getReportData($report)
    {
        $report = $this->getReport($report);
        $data = array(
            'title'        => $report->getTitle(),
            'id'           => $report->getId(),
            'dasbhoard_id' => $report->getDashboard()->getId(),
            'loaded'       => false,
            'widgets'      => $report->getWidgets(),
        );
        return $data;
    }

    public function getLastSortOrder($dashboard) {
        $dashboard = $this->getDashboard($dashboard);
        /** @var ArrayCollection $allReports */
        $allReports = $dashboard->getReports();
        return $allReports->last() ? $allReports->last()->getSortOrder() + 1 : 1;
    }

    ///
    /// WIDGETS SECTION
    ///

    public function getWidgetData($widget)
    {
        if(! ($widget instanceof DashboardWidgetEntity)) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget);
        }
        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $report = $widget->getReport();
        $widget_data = Display::renderQuery('json', $report->query);

        $data = array(
            'id'    => $widget->getId(),
            'name'  => $widget->getTitle(),
            "row"   => $pos[0],
            "col"   => $pos[1],
            "sizeX" => $size[0],
            "sizeY" => $size[1],
            "type"  => "graph",
            "data"  => $widget_data,
        );
        return $data;
    }

}