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

	/**
	 * Calculate the Difference
	 *
	 * @param number $previous_value The previous value
	 * @param number $current_value The current value
	 * @param bool $as_percentage Get the difference as a percentage
	 * @return number The difference
	 */
	public function calculateDifference($previous_value, $current_value, $as_percentage = false)
	{
		$difference = 0;
		if ($previous_value != 0) {
			$difference = ($current_value - $previous_value) / $previous_value;
		}

		return ($as_percentage) ? number_format($difference * 100, 2) : number_format($difference, 2);
	}
}