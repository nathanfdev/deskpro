<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a simple variation change chart
 */
class SimpleVariationChart extends BaseAbstractChart
{
	const CHART_IDENTIFIER = 'simpleVariation';

	/**
	 * The data points
	 *
	 * @var array
	 */ 
	protected $data_points = array();
	
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

	/***
	 * Set the data points
	 *
	 * @param array $data_points The data points
	 */
	public function addDataPoints(array $data_points)
	{
		if (true === is_array($data_points)) {
			$this->data_points = $data_points;
		}
		else {
			$this->data_points = array();
		}
	}

	/**
	 * Get the data points
	 *
	 * @return array The data points
	 */
	public function getDataPoints()
	{
		return $this->data_points;
	}

	/**
	 * Get the first data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getFirstDataPoint($value = true)
	{
		if (0 === count($this->data_points)) {
			return null;
		}
		
		$point = array_slice($this->data_points, 0, 1);

		if ($value) {
			return $point[key($point)];
		}
		else {
			return date("F j", strtotime(key($point)));
		}
	}

	/**
	 * Get the last data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getLastDataPoint($value = true)
	{
		if (0 === count($this->data_points)) {
			return null;
		}
		
		$point = array_slice($this->data_points, -1, 1);

		if ($value) {
			return $point[key($point)];
		}
		else {
			return date("F j", strtotime(key($point)));
		}
	}

	/**
	 * Calculate the Variance
	 *
	 * @param bool $as_percentage Get the variance as a percentage
	 * @return number The variance
	 */
	public function calculateVariation($as_percentage = false)
	{
		$first_value = $this->getFirstDataPoint();
		$last_value  = $this->getLastDataPoint();
		
		// No values, cannot calculate variations
		if (true === is_null($first_value) || true === is_null($last_value)) {
			return '-';
		}
		
		$variation = 0;
		if ($first_value != 0) {
			$variation = ($last_value - $first_value) / $first_value;
		}

		return ($as_percentage) ? number_format($variation * 100, 2) : $variation;
	}
	
	/**
	 * Is the chart ready to be rendered, ie do it have all the data it needs
	 *
	 * @var bool
	 */
	public function isChartRenderable()
	{
		$renderable = false;
		
		// We need at least 2 data points
		if (count($this->data_points) >= 2) {
			$renderable = true;
		}
		
		return $renderable;
	}
}
