<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;
use Application\ReportBundle\Chart;

class ChartController extends AbstractController
{
	public function getChartAction($dashboard_stat_id)
	{
		$dashboardStat = $this->getDashboard($dashboard_stat_id);
		
		$chart = new AmChart\LineChart($dashboardStat->getStat());
		
		$template = 'AmChart/' . $chart->getIdentifier() . '.html.twig';
		
		return $this->render("ReportBundle:Chart:$template", array(
			'data'	=> $chart->getFormattedData(),
		));
	}
	
	/**
	 * Get the Dashboard Stat Entity
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getDashboardStat($dashboard_stat_id)
	{
		$dashboardStat = App::getEntityRepository('DeskPRO:ReportDashboardStat')->find($dashboard_stat_id);
		if (!$dashboardStat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_dashboard_stat");
		}
		
		return $dashboardStat;
	}
}
