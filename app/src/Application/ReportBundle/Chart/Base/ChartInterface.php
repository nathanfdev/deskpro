<?php

namespace Application\ReportBundle\Chart\Base;

interface ChartInterface
{
	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel();
	
	/**
	 * Is the chart ready to be rendered, ie do it have all the data it needs
	 *
	 * @var bool
	 */
	public function isChartRenderable();
	
}