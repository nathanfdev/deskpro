<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard as ReportDashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as ReportDashboardReportEntity;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportDashboardReport.
 */
class ReportDashboardReport
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Tab title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $sortOrder;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\ReportDashboard>")
     *
     * @var ReportDashboardEntity
     */
    private $dashboard;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $variables;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport")
     *
     * @var ScheduledReport
     */
    private $schedule;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $widgets;

    /**
     * Constructor.
     *
     * @param ReportDashboardReportEntity $entity
     * @param Person                      $person
     */
    public function __construct(ReportDashboardReportEntity $entity, Person $person = null)
    {
        $this->id        = $entity->getId();
        $this->title     = $entity->getTitle();
        $this->sortOrder = $entity->getSortOrder();
        $this->dashboard = $entity->getDashboard();

        if ($person && $overrideVars = $person->getPref("reports.dashboards.report.{$entity->getId()}.vars")) {
            $this->variables = $entity->getVariables($overrideVars);
        } else {
            $this->variables = $entity->getVariables();
        }

        if ($person) {
            $this->schedule = $entity->getPersonSchedule($person);
        }
    }

    /**
     * @param InlineCustomSideload $widgets
     */
    public function setWidgets(InlineCustomSideload $widgets)
    {
        $this->widgets = $widgets;
    }
}
