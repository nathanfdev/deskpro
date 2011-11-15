<?php

namespace Application\ReportBundle\Chart\DeskPRO;

/**
 * Display a detailed drill down change chart
 */
class DetailedDrillDownChart extends AbstractDrillDownChart
{
	const CHART_IDENTIFIER = 'detailedDrillDown';

	/**
	 * The chart series
	 *
	 * @var array
	 */
	protected $series = array();

	public function __construct()
	{
		$this->view_chart_vendor 	= 'DeskPRO';
		$this->view_chart_class 	= 'DetailedDrillDown';
	}

	/**
	 * Get the Human Friendly label for the chart
	 */
	public static function getChartLabel()
	{
		return 'Detailed Drilldown Chart';
	}

	/**
	 * Get the first data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getFirstDataPoint($row, $value = true)
	{
		if (0 === count($row)) {
			return null;
		}
		
		$point = array_slice($row, 0, 1);

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
	public function getLastDataPoint($row, $value = true)
	{
		if (0 === count($row)) {
			return null;
		}
		
		$point = array_slice($row, -1, 1);

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
	public function calculateVariation($row, $as_percentage = false)
	{
		$first_value = $this->getFirstDataPoint($row);
		$last_value  = $this->getLastDataPoint($row);
		
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
	 * Adds a series to the chart
	 *
	 * @param string $series The series label
	 */
	public function addSeries($series)
	{
		$this->series[] = $series;
	}

	/**
	 * Get the series
	 *
	 * @return array The series
	 */
	public function getSeries($limit = null)
	{
		if (true === is_null($limit)) {
			return $this->series;
		}
		else {
			return array_slice($this->series, ($limit * -1), $limit);
		}
	}

	/**
	 * Gets the values of data from the end of the data set
	 *
	 * @param int $limit The number of series labels to get
	 */
	public function getEndSeries($limit)
	{
		if (0 === count($this->rows)) {
			return array();
		}
		
		// Get the series from the first row of data
		$row = array_slice($this->rows, 0, 1);
		$values = array_slice($row[0]['data'], ($limit * -1), $limit);

		$series = array();
		foreach (array_keys($values) as $time) {
			$series[] = date('M j', strtotime($time));
		}
		
		return $series;
	}

	/**
	 * Gets the values of data from the end of the data set
	 *
	 * @param array $row The row to work with
	 * @param int $label The number of values to get
	 */
	public function getEndData($row, $limit)
	{
		$values = array();

		return array_slice($row['data'], ($limit * -1), $limit);
	}
}
