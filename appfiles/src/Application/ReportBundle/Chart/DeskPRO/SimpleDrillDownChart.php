<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a simple drill down change chart
 */
class SimpleDrillDownChart extends BaseAbstractChart
{
	const CHART_IDENTIFIER = 'simpleDrillDown';

	public function __construct()
	{
		$this->view_chart_vendor 	= 'DeskPRO';
		$this->view_chart_class 	= 'SimpleDrillDown';
	}

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Simple Drilldown Chart';
	}
}
