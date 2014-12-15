<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ReportDashboardReport as DashboardReport;

/**
 * @SWG\Resource(
 * 	resourcePath="/dashboards/reports",
 * 	description="Operations about Reports",
 * 	basePath="/api"
 * )
 */
class DashboardReportController extends AbstractController
{
    /**
     * @SWG\Api(
     * 	path="/dashboards/reports",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Get all reports available",
     *		type="array",
     * 	)
     * )
     */

    public function listAction()
    {
        $data = array();
        $reports = $this->em->getRepository('DeskPRO:ReportDashboardReport')->findAll();

        foreach ($reports as $k => $report) {
            /** @var DashboardReport $report */
            $data[$k] = $this->_getReportData($report);
        }
        return $this->createApiResponse($data);
    }

    /**
     * @param $report
     *
     * @return null|DashboardReport
     */
    protected function _getReport($report)
    {
        if(! ($report instanceof DashboardReport)) {
            $report = $this->em->getRepository('DeskPRO:ReportDashboardReport')->find((int) $report);
            if (!$report) throw $this->createNotFoundException('DashboardReport not found!');
        }
        return $report;
    }

    protected function _getReportData($report)
    {
        $report = $this->_getReport($report);
        $data = array(
            'title'   => $report->getTitle(),
            'id'      => $report->getId(),
            'loaded'  => false,
            'widgets' => $report->getWidgets(),
        );
        return $data;
    }
}
