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
	 * The RAW chart data
	 */
	protected $data = array();

	/**
	 * List of labels
	 *
	 * @var array
	 */
	protected $labels = array();

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
	 * Get the labels for the chart
	 *
	 * @return array List of labels
	 */
	public function getLabels()
	{
		return $this->labels;
	}

	/**
	 * Get a label by its reference index
	 *
	 * @param int $index
	 * @return string
	 */
	public function getLabel($index)
	{
		return (isset($this->labels[$index])) ? $this->labels[$index] : null;
	}
}