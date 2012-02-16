<?php

namespace Application\ReportBundle\Chart\AmChart;

class StackedColumnChart extends AmColumnChart
{
	const CHART_IDENTIFIER = 'stackedColumn';

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Stacked Column Chart';
	}
}