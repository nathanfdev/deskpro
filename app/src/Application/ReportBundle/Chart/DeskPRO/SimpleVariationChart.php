<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

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

	/**
	 * Is a postive difference value good, bad or neutral
	 *
	 * @var string
	 */
	protected $difference_direction = 'neutral';

	/**
	 * @var array
	 */
	protected $date_mode = 'day';

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
		return 'Difference Chart';
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

		$this->setMinMaxValues(max($data_points));
		$this->setMinMaxValues(min($data_points));

		$first_key  = \Orb\Util\Arrays::getNthKey($this->data_points, 0);
		$second_key = \Orb\Util\Arrays::getNthKey($this->data_points, 1);
		$diff = abs($second_key - $first_key);

		if ($diff >= 2592000) {
			$this->date_mode = 'month';
		} elseif ($diff >= 86400) {
			$this->date_mode = 'day';
		} else {
			if (count($data_points) >= 24) {
				$this->date_mode = 'day_hour';
			} else {
				$this->date_mode = 'hour';
			}
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
		return $this->getDataPoint(0, $value);
	}

	/**
	 * Get the last data point
	 *
	 * @param bool $value True to return the vaule, false to return the label
	 */
	public function getLastDataPoint($value = true)
	{
		return $this->getDataPoint(0, $value, true);
	}

	/**
	 * Get a data point by index
	 *
	 * @param int $index The index to return (starts at 0). Is $reverse is true
	 *                   index counts from end of array (ie, index 2 would
	 *                   get the 2nd from last element)
	 * @param bool $value True to return the vaule, false to return the label
	 * @param bool $reverse True to search from the end of the array
	 */
	public function getDataPoint($index, $value = true, $reverse = false)
	{
		if (0 === count($this->data_points)) {
			return null;
		}

		if (true === $reverse) {
			$point = array_slice($this->data_points, (($index+1) * -1), 1);
		}
		else {
			$point = array_slice($this->data_points, $index, 1);
		}

		if ($value) {
			return $point[key($point)];
		} else {
			$time = strtotime(key($point));
			switch ($this->date_mode) {
				case 'month': return date('F', $time);
				case 'day': return date('F jS', $time);
				case 'day_hour': return date('jS ga', $time);
				case 'hour': return date('ga', $time);
			}
			return '';
		}
	}

	/**
	 * Calculate the Variance
	 *
	 * @param bool $as_percentage Get the variance as a percentage
	 * @return number The difference
	 */
	public function getDifference($as_percentage = false)
	{
		$previous_value = $this->getDataPoint(1, true, true);
		$current_value  = $this->getLastDataPoint();

		// No values, cannot calculate variations
		if (true === is_null($previous_value) || true === is_null($current_value)) {
			return null;
		}

		return $this->calculateDifference($previous_value, $current_value, $as_percentage);
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
