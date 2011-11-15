<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a simple variation change chart
 */
class SimpleVariationChart extends BaseAbstractChart
{
	const CHART_IDENTIFIER = 'simpleVariation';

	public function __construct()
	{
		$this->view_chart_vendor 	= 'DeskPRO';
		$this->view_chart_class 	= 'SimpleVariation';
	}

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Variation Chart';
	}
}
