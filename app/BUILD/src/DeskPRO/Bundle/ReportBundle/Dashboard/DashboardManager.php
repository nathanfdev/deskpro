<?php

namespace DeskPRO\Bundle\ReportBundle\Dashboard;

use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardWidget;

/**
 * Class DashboardManager.
 */
class DashboardManager
{
    /**
     * @param ReportDashboardReport $report
     *
     * @return ReportDashboardReport
     */
    public function cloneReport(ReportDashboardReport $report)
    {
        $cloneReport = new ReportDashboardReport();
        $cloneReport->setTitle($report->getTitle().' (copy)');
        $cloneReport->setDashboard($report->getDashboard());
        $cloneReport->setVariables($report->getVariables());
        $cloneReport->setSortOrder($report->getSortOrder());

        foreach ($report->getWidgets() as $widget) {
            $cloneWidget = new ReportDashboardWidget();
            $cloneWidget
                ->setTitle($widget->getTitle())
                ->setCol($widget->getCol())
                ->setRow($widget->getRow())
                ->setSizeX($widget->getSizeX())
                ->setSizeY($widget->getSizeY())
                ->setType($widget->getType())
                ->setVariables($widget->getVariables())
                ->setReport($cloneReport)
                ->setWidget($widget->getWidget())
            ;

            $cloneReport->addWidget($cloneWidget);
        }

        return $cloneReport;
    }

    /**
     * @param ReportDashboard $dashboard
     *
     * @return ReportDashboard
     */
    public function cloneDashboard(ReportDashboard $dashboard)
    {
        $cloneDashboard = new ReportDashboard();
        $cloneDashboard->setTitle($dashboard->getTitle().' (copy)');
        $cloneDashboard->setIsDefault(false);

        foreach ($dashboard->getReports() as $report) {
            $cloneDashboard->addReport($this->cloneReport($report));
        }

        foreach ($dashboard->getPermissions() as $permission) {
            $clonePermission = new ReportDashboardPermission();
            $clonePermission
                ->setDashboard($cloneDashboard)
                ->setPerson($permission->getPerson())
                ->setName($permission->getName())
            ;

            $cloneDashboard->getPermissions()->add($clonePermission);
        }

        return $cloneDashboard;
    }
}
