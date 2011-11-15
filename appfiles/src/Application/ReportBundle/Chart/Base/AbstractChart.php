<?php

namespace Application\ReportBundle\Chart\Base;

abstract class AbstractChart implements ChartInterface
{
	/**
	 * Unique identifier of chart type
	 */
	const CHART_IDENTIFIER = '';

	protected $chart_type = '';

	protected $chart_vendor = '';

	/**
	 * Get the view chart vendor
	 *
	 * @return string The view chart vendor
	 */
	public function getViewChartVendor()
	{
		return $this->view_chart_vendor;
	}

	/**
	 * Get the view chart class
	 *
	 * @return string The view chart class
	 */
	public function getViewChartClass()
	{
		return $this->view_chart_class;
	}
	
	/**
	 * Text string to display when no data is available
	 *
	 * @return string
	 */
	public function getNoDataLabel()
	{
		return "No data for available for period";
	}
}