<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30
 */

namespace Application\ApiBundle\Service;

use \Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReportEntity;

class Dashboard
{
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $dashboard
     *
     * @return null|DashboardEntity
     */
    public function getDashboard($dashboard) {
        if(! ($dashboard instanceof Dashboard)) {
            $dashboard = $this->em->getRepository('DeskPRO:ReportDashboard')->find((int) $dashboard);
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
}