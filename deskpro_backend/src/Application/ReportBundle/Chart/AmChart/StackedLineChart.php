<?php

namespace Application\ReportBundle\Chart\AmChart;

class StackedLineChart extends AmLineChart
{
	const CHART_IDENTIFIER = 'stackedLine';

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Stacked Line Chart';
	}
}