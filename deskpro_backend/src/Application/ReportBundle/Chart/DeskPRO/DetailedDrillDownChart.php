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

	/**
	 * Is a postive difference value good, bad or neutral
	 *
	 * @var string
	 */
	protected $difference_direction = 'neutral';

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
		return $this->getDataPoint($row, 0, $value);
	}

	/**
	 * Get the last data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getLastDataPoint($row, $value = true)
	{
		return $this->getDataPoint($row, 0, $value, true);
	}

	/**
	 * Get an array by index
	 *
	 * @param array $row The array to operate on
	 * @param int $index The index to return (starts at 0). Is $reverse is true
	 *                   index counts from end of array (ie, index 2 would
	 *                   get the 2nd from last element)
	 * @param bool $value True to return the vaule, false to return the label
	 * @param bool $reverse True to search from the end of the array
	 */
	public function getDataPoint($row, $index, $value = true, $reverse = false)
	{
		if (0 === count($row)) {
			return null;
		}

		if (true === $reverse) {
			$point = array_slice($row, (($index+1) * -1), 1);
		}
		else {
			$point = array_slice($row, $index, 1);
		}

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
	public function getDifference($row, $as_percentage = false)
	{
		$previous_value = $this->getDataPoint($row, 1, true, true);
		$current_value  = $this->getLastDataPoint($row);

		// No values, cannot calculate variations
		if (true === is_null($previous_value) || true === is_null($current_value)) {
			return null;
		}

		return $this->calculateDifference($previous_value, $current_value, $as_percentage);
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
		return array_slice($row['data'], ($limit * -1), $limit);
	}

	/**
	 * Set the difference directions
	 *
	 * @param string $difference_direction
	 */
	public function setDifferenceDirection($difference_direction)
	{
		$this->difference_direction = $difference_direction;
	}

	/**
	 * Get the difference directions
	 *
	 * @return string
	 */
	public function getDifferenceDirection()
	{
		return $this->difference_direction;
	}
}
