<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use Application\DeskPRO\Entity\ReportWidget;

class MattersPlusData extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     */
    public function runInstall()
    {
    }

    private function makeWidget(ReportDashboardReport $report, $title, $pos, $size, $type, $statId, $statTitle, $query, $queryVars = [], $widgetVars = [], $widgetOptions = [])
    {
        $widget = $this->getEm()->getRepository(ReportWidget::class)->findOneBy(['unique_key' => $statId]);

        if (!$widget) {
            $widget = new ReportWidget();
        }

        $widget->setTitle($title);
        $widget->setUniqueKey($statId);
        $widget->setDescription('');
        $widget->setIsCustom(false);
        $widget->setVariables($queryVars);
        $widget->setQuery($query);

        $this->getEm()->persist($widget);
        $this->getEm()->flush();

        $reportWidget = $report->getWidgets()->filter(function (ReportDashboardWidget $w) use ($statId) {
            return $w->getWidget()->getUniqueKey() === $statId;
        })->first();

        if (!$reportWidget) {
            $reportWidget = new ReportDashboardWidget();
            $reportWidget->setReport($report);
            $report->addWidget($reportWidget);
        }

        $reportWidget->setTitle($title);
        $reportWidget->setSize($this->getSizeFor($size));
        $reportWidget->setPosition($this->getSizeFor($pos));
        $reportWidget->setType($type);
        $reportWidget->setVariables($widgetVars);
        $reportWidget->setOptions(json_encode($widgetOptions ?: []));

        $this->getEm()->persist($reportWidget);
        $this->getEm()->flush();
    }

    /**
     * @param string $input e.g. 2:2
     *
     * @return string e.g. 8:8
     */
    private function getSizeFor($input)
    {
        $unitSize = 4;

        return implode(':', array_map(function ($size) use ($unitSize) {
            return $unitSize * $size;
        }, explode(':', $input)));
    }

    /**
     * @param ReportDashboard $dash
     * @param int             $id    This is the ordinal, but we re-use it as an 'id' of sorts for updates
     * @param string          $title
     *
     * @return ReportDashboardReport
     */
    private function makeReport(ReportDashboard $dash, $id, $title)
    {
        $report = $dash->getReports()->get($id);

        if (!$report) {
            $report = new ReportDashboardReport();
            $dash->addReport($report);
            $report->setDashboard($dash);
        }

        $report->setDashboard($dash);
        $report->setSortOrder($id);
        $report->setTitle($title);

        $this->getEm()->persist($report);

        return $report;
    }

    /**
     * @param string $title
     * @param string $systemName
     * @param int    $displayOrder
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return ReportDashboard
     */
    private function makeDashboard($title, $systemName, $displayOrder)
    {
        $dash = $this->getEm()->getRepository(ReportDashboard::class)->findOneBy(['system_name' => $systemName]);

        if (!$dash) {
            $dash = new ReportDashboard();
        }

        $dash->setTitle($title);
        $dash->setSystemName($systemName);
        $dash->setDisplayOrder($displayOrder);
        $dash->setIsDefault(true);

        $hasGlobalView = count($dash->getPermissions()->filter(function (ReportDashboardPermission $p) {
            return $p->getPerson() === null && $p->getTeam() === null && $p->getDepartment() === null;
        }));

        if (!$hasGlobalView) {
            $perm = new ReportDashboardPermission();
            $perm->setName(ReportDashboardPermission::FULL);
            $perm->setDashboard($dash);
            $dash->getPermissions()->add($perm);
        }

        $this->getEm()->persist($dash);
        $this->getEm()->flush();

        return $dash;
    }

    /**
     * {@inheritdoc}
     */
    public function runSync()
    {
        $this->runInstall();
    }
}
