<?php

namespace Application\ReportBundle\Chart\AmChart;

use Application\ReportBundle\Chart\Base\PieChart as BasePieChart;

/**
 * Pie Chart representation
 */
class PieChart extends BasePieChart
{
	public function __construct()
	{
		$this->view_chart_vendor 	= 'AmChart';
		$this->view_chart_class 	= 'Pie';
	}

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Pie Chart';
	}
}