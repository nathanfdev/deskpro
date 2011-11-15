<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a detailed drill down change chart
 */
class DetailedDrillDownChart extends BaseAbstractChart
{
	const CHART_IDENTIFIER = 'detailedDrillDown';

	public function __construct()
	{
		$this->view_chart_vendor 	= 'DeskPRO';
		$this->view_chart_class 	= 'DetailedDrillDown';
	}

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Detailed Drilldown Chart';
	}
}
