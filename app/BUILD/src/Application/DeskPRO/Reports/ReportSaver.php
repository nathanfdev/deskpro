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

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\SavedDashboardReport;
use Application\DeskPRO\Entity\SavedDashboardWidget;
use Application\LegacyApiBundle\Service\DashboardWidget;
use Doctrine\ORM\EntityManager;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * Class ReportsSaver.
 */
class ReportSaver
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
     * @param EntityManager   $em
     * @param DashboardWidget $widgetService
     */
    public function __construct(EntityManager $em, DashboardWidget $widgetService)
    {
        $this->em            = $em;
        $this->widgetService = $widgetService;
    }

    /**
     * @param DashboardReportEntity $report
     *
     * @throws \Exception
     */
    public function saveReport(DashboardReportEntity $report)
    {
        $savedReport = new SavedDashboardReport();
        $savedReport
            ->setColumns($report->getColumns())
            ->setTitle($report->getTitle())
            ->setVariables($report->getVariables())
            ->setAuthcode($report->getId().DpStrings::random(10, Strings::CHARS_KEY_ALPHA));

        foreach ($report->getWidgets() as $widget) {
            $widgetData  = $this->widgetService->renderWidgetQuery($widget);
            $savedWidget = new SavedDashboardWidget();
            $savedWidget
                ->setVariables($widget->getVariables())
                ->setTitle($widget->getTitle())
                ->setDashboardWidget($widget)
                ->setOptions($widget->getOptions())
                ->setSize(implode(':', $widget->getSize()))
                ->setPosition($widget->getPosition())
                ->setSavedReport($savedReport)
                ->setData($widgetData)
                ->setType($widget->getType())
            ;

            $savedReport->addSavedWidget($savedWidget);
        }
    }
}
