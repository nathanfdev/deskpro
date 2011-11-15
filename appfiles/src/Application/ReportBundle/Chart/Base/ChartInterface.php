<?php

namespace Application\ReportBundle\Chart\Base;

interface ChartInterface
{
	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel();
}