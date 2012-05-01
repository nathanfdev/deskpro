<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage AdminBundle
 */

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;
use Application\ReportBundle\Chart\ChartFactory;
use Application\ReportBundle\Chart\AmChart\LineChart;
use Application\DeskPRO\Entity\ReportDashboard;

class ChartController extends AbstractController
{
	public function getChartAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$end_date = new \DateTime();
		$chart_type = $this->getRequest()->get('chart_type', '');
		if (false === $this->getRequest()->get('all', false)) {
			$points = $dashboard_stat->getNumberDataPoints();
		}
		else {
			$points = $dashboard_stat->getStat()->getMaxDataPointCount();
		}

		// Get the Stat Data
		$data = $dashboard_stat->getStat()->getData($end_date, $points, $dashboard_stat->getDisplayGrouping());

		if ($dashboard_stat->getDisplayGrouping()) {
			$chart_data = $data['grouped'];
		}
		else {
			$chart_data = array($data['ungrouped']);
		}

		if (strlen($chart_type)) {
			$classes = ReportDashboard::getChartClasses();
			$chart_class = $classes[$chart_type];
		}
		else {
			$chart_class = $dashboard_stat->getViewClass();
		}
		$chart = ChartFactory::getChart($chart_class, $chart_data, $dashboard_stat->getStat());

		$chart_template = $chart->getViewChartVendor() . '/' . $chart::CHART_IDENTIFIER . '.html.twig';

		return $this->render("ReportBundle:Chart:$chart_template", array(
			'dashboard_stat' => $dashboard_stat,
			'chart'          => $chart,
		));
	}

	public function getChartSettingsAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$stat = $dashboard_stat->getStat();
		if (!$stat->last_run) {
			$this->processFirstData($stat);
		}

		$end_date = new \DateTime();

		$chart_type = $this->getRequest()->get('chart_type', '');
		if (false === $this->getRequest()->get('all', false)) {
			$points = $dashboard_stat->getNumberDataPoints();
		}
		else {
			$points = $dashboard_stat->getStat()->getMaxDataPointCount();
		}

		// Get the Stat Data
		$data = $dashboard_stat->getStat()->getData($end_date, $points, $dashboard_stat->getDisplayGrouping());

		if ($dashboard_stat->getDisplayGrouping()) {
			$chart_data = $data['grouped'];
		}
		else {
			$chart_data = array($data['ungrouped']);
		}

		if (strlen($chart_type)) {
			$classes = ReportDashboard::getChartClasses();
			$chart_class = $classes[$chart_type];
		}
		else {
			$chart_class = $dashboard_stat->getViewClass();
		}
		$chart = ChartFactory::getChart($chart_class, $chart_data, $dashboard_stat->getStat());
		$settings_template = $chart->getViewChartVendor() . '/Settings/' . $chart::CHART_IDENTIFIER . '.xml.twig';

		return $this->render("ReportBundle:Chart:$settings_template", array(
			'dashboard_stat' => $dashboard_stat,
			'chart'          => $chart
		));
	}

	protected function processFirstData(\Application\DeskPRO\Entity\Stat $stat)
	{
		$stat_concept_class = $stat->getStatConceptClass();

		$stat_concept = new $stat_concept_class($stat, $stat->getLastFullRun());

		//$logger = new \Application\DeskPRO\Log\Logger();
		//$logger->addWriter(new \Orb\Log\Writer\ErrorLog());
		//$stat_concept->setLogger($logger);

		$stat_concept->addGrouping($stat->getGroupingRef());

		$dt = new \DateTime('-1 hours');
		$dt->setTime($dt->format('H'), 0, 0);
		$values = $stat_concept->getStats($dt);

		// Check if existing StatValue is set for period
		$stat_value = $stat->getStatValueForDate(new \DateTime());
		if (!$stat_value) {
			// Create a new one
			$stat_value = new \Application\DeskPRO\Entity\StatValue();
			$stat_value->setStat($stat);
		}
		$stat_value->setValue($values['ungrouped']);
		$stat_value->setStatUnix(time());
		$this->em->persist($stat_value);

		// Store the grouped values
		foreach ($values['grouped'] as $grouped) {
			// Check if existing StatValueGroup is set for period and reference
			$stat_value_group = $stat_value->getStatValueGroupForDate(
				new \DateTime(),
				$grouped['grouping_ref'],
				$stat->getRunFrequency()
			);

			if (!$stat_value_group) {
				$stat_value_group = new \Application\DeskPRO\Entity\StatValueGroup();
				$stat_value_group->setStatValue($stat_value);
			}
			$stat_value_group->setValue($grouped['value']);
			$stat_value_group->setGroupingRef($grouped['grouping_ref']);
			$stat_value_group->setStatUnix(time());
			$this->em->persist($stat_value_group);
		}

		$stat->last_run = new \DateTime();
		$this->em->persist($stat);
		$this->em->flush();
	}

	/**
	 * Get the Fullscreen details for a chart, some charts when going fullscreen
	 * are transformed to use other charts
	 */
	public function getChartFullscreenDetailsAction($dashboard_stat_id)
	{
		$dashboard_stat = $this->getDashboardStat($dashboard_stat_id);

		$chart_class = ChartFactory::transformChartToFullScreen($dashboard_stat->getViewClass());
		$chart = new $chart_class;

		$details = array(
			'dashboard_stat_id' => $dashboard_stat->getId(),
			'chart_vendor'	    => $chart->getViewChartVendor(),
			'chart_class'       => $chart->getViewChartClass(),
			'chart_type'	    => ReportDashboard::getChartClassIndex($chart_class),
		);

		return $this->createJsonResponse(array('chart' => $details));
	}

	/**
	 * Get the Dashboard Stat Entity
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getDashboardStat($dashboard_stat_id)
	{
		$dashboardStat = $this->em->getRepository('DeskPRO:ReportDashboardStat')->find($dashboard_stat_id);
		if (!$dashboardStat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("error_404_dashboard_stat");
		}

		return $dashboardStat;
	}
}
