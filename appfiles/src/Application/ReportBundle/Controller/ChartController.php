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
use Application\ReportBundle\Chart\AmChart\LineChart;

class ChartController extends AbstractController
{
	public function getChartAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$view_class = $dashboard_stat->getViewClass();
		$chart = new $view_class($dashboard_stat->getStat());
		$chart->setDataEndDate(\DateTime());
		
		$chart_template = $chart->getViewChartVendor() . '/' . $chart::CHART_IDENTIFIER . '.html.twig';

		return $this->render("ReportBundle:Chart:$chart_template", array(
			'dashboard_stat' => $dashboard_stat,
			'chart'          => $chart,
		));
	}

	public function getChartDataAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$view_class = $dashboard_stat->getViewClass();
		$chart = new $view_class($dashboard_stat->getStat());
		$chart->setDataEndDate(\DateTime());

		$data_template = $chart->getViewChartVendor() . '/Data/' . $chart::CHART_IDENTIFIER . '.xml.twig';

		return $this->render("ReportBundle:Chart:$data_template", array(
			'data' => $chart->getFormattedData(),
		));
	}

	public function getChartSettingsAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$view_class = $dashboard_stat->getViewClass();
		$chart = new $view_class($dashboard_stat->getStat());
		$chart->setDataEndDate(\DateTime());

		$settings_template = $chart->getViewChartVendor() . '/Settings/' . $chart::CHART_IDENTIFIER . '.xml.twig';

		return $this->render("ReportBundle:Chart:$settings_template", array(
			'chart' => $chart
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
