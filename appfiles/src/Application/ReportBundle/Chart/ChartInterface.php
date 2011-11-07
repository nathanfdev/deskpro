<?php

namespace Application\ReportBundle\Chart;

interface ChartInterface
{
	
	/**
	 * Get the formated data for the chart, ready for the view
	 *
	 * @return array The formatted data
	 */
	public function getFormattedData();
	
}