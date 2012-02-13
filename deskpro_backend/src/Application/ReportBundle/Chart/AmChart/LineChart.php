<?php

namespace Application\ReportBundle\Chart\AmChart;

class LineChart extends AmLineChart
{
	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Line Chart';
	}
}