<?php

namespace Application\ReportBundle\Chart\AmChart;

class ColumnChart extends AmColumnChart
{
	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Column Chart';
	}
}