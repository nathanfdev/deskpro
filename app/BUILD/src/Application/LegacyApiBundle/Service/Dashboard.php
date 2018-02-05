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

namespace Application\LegacyApiBundle\Service;

use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Translate\Translate;
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
    /// REPORTS SECTION
    ///

    protected function getReportLevelVar(ReportDashboardWidget $widget, $name)
    {
        $data             = [];
        $globalWidgetVars = $widget->getWidget()->getVariables();
        $reportLevelVars  = $widget->getReport()->getVariables();
        foreach ($globalWidgetVars as $globalWidgetVar) {
            if ($globalWidgetVar['name'] === $name) {
                $data          = $globalWidgetVar;
                $data['value'] = isset($reportLevelVars[$name]) ? $reportLevelVars[$name]['value'] : null;
            }
        }

        return $data;
    }

    /**
     * @param $report
     *
     * @return array
     */
    public function getReportData($report)
    {
        $report                   = $this->getReport($report);
        $widgets                  = [];
        $availableReportLevelVars = [];
        foreach ($report->getWidgets() as $widget) {
            $wdata = $this->widgetService->getWidgetData($widget);
            foreach ($wdata['widget_variables'] ?: [] as $name => $widgetVariable) {
                if ($widgetVariable['value'] === DashboardWidget::WIDGET_VALUE_FROM_REPORT) {
                    $availableReportLevelVars[$name] = $this->getReportLevelVar($widget, $name);
                }
            }
            $widgets[] = $wdata;
        }

        $data = [
            'title'        => $report->getTitle(),
            'id'           => $report->getId(),
            'dashboard_id' => $report->getDashboard()->getId(),
            'loaded'       => true,
            'options'      => [
                'floating' => false,
                'swapping' => false,
            ],
            'widgets'   => $widgets,
            'variables' => array_values($availableReportLevelVars),
        ];

        return $data;
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
     * @param string $label
     *
     * @return string
     */
    public function mapLabelToSystemName($label)
    {
        return isset($this->labelsStorage[$label]) ? $this->labelsStorage[$label] : strtolower($label);
    }
}
