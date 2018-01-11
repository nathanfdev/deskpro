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
