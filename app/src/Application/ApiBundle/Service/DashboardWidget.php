<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.01.2015
 * Time: 3:24
 */

namespace Application\ApiBundle\Service;

use \Doctrine\ORM\EntityManager;

use Application\DeskPRO\DataSync\ReportWidget;
use Application\DeskPRO\Entity\ReportDashboardWidget as DashboardWidgetEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;

class DashboardWidget
{

    const OUTER_TYPE_OVERVIEW            = 'overview';
    const OUTER_TYPE_PERFORMANCE         = 'performance';
    const OUTER_TYPE_TICKET_SATISFACTION = 'ticket_satisfaction';

    const WIDGET_TYPE_HARDCODED_OVERVIEW            = 'reports_overview';
    const WIDGET_TYPE_HARDCODED_PERFORMANCE         = 'agent_performance';
    const WIDGET_TYPE_HARDCODED_TICKET_SATISFACTION = 'ticket_satisfaction';
    const WIDGET_TYPE_HARDCODED_UNDEFINED           = 'hardcoded';

    const WIDGET_TYPE_GRAPH = 'graph';
    const WIDGET_TYPE_TABLE = 'table';
    const WIDGET_TYPE_STAT  = 'stat';


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function changeDisplayType(ReportWidget $reportWidget, DashboardWidgetEntity $dashboardWidget)
    {

    }

    public function getWidgetData($widget)
    {
        if(! ($widget instanceof DashboardWidgetEntity)) {
            $widget = $this->em->getRepository('DeskPRO:ReportDashboardWidget')->find((int) $widget);
        }
        $pos  = $widget->getPosition();
        $size = $widget->getSize();
        $data = array(
            'id'    => $widget->getId(),
            'name'  => $widget->getTitle(),
            "row"   => $pos[0],
            "col"   => $pos[1],
            "sizeX" => $size[0],
            "sizeY" => $size[1],
            "widget_id" => $widget->getReport() ? $widget->getReport()->getId() : 0,
            "widget_variables" => $widget->getVariables(),
            "type"  => "graph",
            "data"  => array(),
        );
        if($hc_data = $widget->getHcData()) {
            switch ($hc_data['outer_type']) {
                case self::OUTER_TYPE_OVERVIEW:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_OVERVIEW;
                    break;
                case self::OUTER_TYPE_PERFORMANCE:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_PERFORMANCE;
                    break;
                case self::OUTER_TYPE_TICKET_SATISFACTION:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_TICKET_SATISFACTION;
                    break;
                default:
                    $data['type'] = self::WIDGET_TYPE_HARDCODED_UNDEFINED;
            }
            $data['inner_type'] = $hc_data['inner_type'];
            $data['outer_type'] = $hc_data['outer_type'];
        }
        return $data;
    }

    public function copyWidgetLinks(DashboardReportEntity $report, DashboardReportEntity $reportPrototype)
    {
        foreach($reportPrototype->getWidgets() as $widget_prototype) {
            $widget = new DashboardWidgetEntity();
            $widget
                ->setTitle($widget_prototype->getTitle())
                ->setPosition($widget_prototype->getPosition())
                ->setSize($widget_prototype->getSize())
                ->setReport($report)
                ->setWidget($widget_prototype->getWidget());
            $this->em->persist($widget);
            $report->addWidget($widget);
        }
    }
}