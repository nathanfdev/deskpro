<?php

namespace DeskPRO\Bundle\ReportBundle\Service;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\SavedDashboardReport;
use Application\DeskPRO\Entity\SavedDashboardWidget;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
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
     * @var DashboardWidgetManager
     */
    private $widgetService;

    /**
     * @param EntityManager          $em
     * @param DashboardWidgetManager $widgetService
     */
    public function __construct(EntityManager $em, DashboardWidgetManager $widgetService)
    {
        $this->em            = $em;
        $this->widgetService = $widgetService;
    }

    /**
     * @param DashboardReportEntity $report
     * @param Person                $person
     *
     * @throws \Exception
     *
     * @return SavedDashboardReport
     */
    public function saveReport(DashboardReportEntity $report, Person $person = null)
    {
        $savedReport = new SavedDashboardReport();
        $savedReport
            ->setTitle($report->getTitle())
            ->setVariables($report->getVariables())
            ->setAuthcode($report->getId().DpStrings::random(10, Strings::CHARS_KEY_ALPHA));

        foreach ($report->getWidgets() as $widget) {
            $reportLevelVars = $report->getVariables();
            $widgetVars      = $widget->getVariables() ?: [];
            foreach ($widgetVars as $key => &$var) {
                if ($var['value'] === DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT
                    && isset($reportLevelVars[$key]) && $reportLevelVars[$key] && $reportLevelVars[$key]['value']) {
                    $var['value'] = $reportLevelVars[$key]['value'];
                }
            }
            $widget->setVariables($widgetVars);
            $widgetData = $this->widgetService->renderWidget($widget, $person);

            if ($widgetData && $widget->getType() == DashboardWidgetManager::WIDGET_TYPE_TABLE) {
                $columns = [];
                foreach ($widgetData['columns'] as $column) {
                    $aoColumns[] = null;
                    $columns[]   = ['title' => $column];
                }
                $widgetData['aoColumns'] = $aoColumns;
                $widgetData['columns']   = $columns;
            }

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
            $this->em->persist($savedWidget);
        }
        $this->em->persist($savedReport);
        $this->em->flush();

        return $savedReport;
    }

    /**
     * @param        $whenSetting
     * @param        $whenTz
     * @param        $frequency
     * @param string $startDate
     *
     * @return \DateTime
     */
    public function calculateNextSendDate($whenSetting, $whenTz, $frequency, $startDate = 'now')
    {
        $timezone = new \DateTimeZone($whenTz);
        $nextDate = new \DateTime($startDate, $timezone);

        switch ($frequency) {
            case ScheduledReport::FREQUENCY_DAILY:
                $nextDate->modify(sprintf('today %s', $whenSetting['time']));
                break;
            case ScheduledReport::FREQUENCY_WEEKLY:
                $nextDate->modify(sprintf('%s %s', $whenSetting['weekday'], $whenSetting['time']));
                break;
            case ScheduledReport::FREQUENCY_MONTHLY:
                $monthday = $whenSetting['monthday'];
                $current  = (int) $nextDate->format('d');
                if ($monthday < $current) {
                    $nextDate->modify('+1 month');
                }
                $nextDate->modify(sprintf(
                        '%s/%\'02d %s',
                        $nextDate->format('Y/m'),
                        $monthday,
                        $whenSetting['time'])
                );
                break;
            case ScheduledReport::FREQUENCY_BIMONTHLY:
                $monthday  = min((int) $whenSetting, (int) $whenSetting['monthday2']);
                $monthday2 = max((int) $whenSetting['monthday'], (int) $whenSetting['monthday2']);
                $current   = (int) $nextDate->format('d');
                switch (true) {
                    case $current > $monthday && $current > $monthday2:
                        // use first date, e.g. today is 23, while report should run 1 and 15
                        // pick up next month
                        $nextDate->modify('+1 month');
                    case $current < $monthday:
                    case $current == $monthday:
                        // use first date, e.g. today is 2, while report should run 1 and 15
                        // use first date, e.g. today is day "X"
                        $pickDate = $monthday;
                        break;
                    case $current > $monthday && $current < $monthday2:
                    case $current == $monthday2:
                        // use second date, e.g. should run 1 and 15, today is 12 or 15

                        $pickDate = $monthday2;
                        break;
                    default:
                        // not sure what should happen to reach this
                        $nextDate->modify('+1 month');
                        $pickDate = $monthday;
                }

                $nextDate->modify(sprintf(
                        '%s/%\'02d %s',
                        $nextDate->format('Y/m'),
                        $pickDate,
                        $whenSetting['time'])
                );
                break;
        }

        // should not be set early than it is now
        $nextDate = $nextDate > new \DateTime('now', $timezone)
            ? $nextDate
            : $this->calculateNextSendDate($whenSetting, $whenTz, $frequency, 'tomorrow');

        return $nextDate->setTimezone(new \DateTimeZone('UTC'));
    }
}
