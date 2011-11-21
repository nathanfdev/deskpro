<?php

namespace Application\ReportBundle\Chart\DeskPRO;

use Application\ReportBundle\Chart\Base\AbstractChart as BaseAbstractChart;

/**
 * Display a drill down change chart
 */
abstract class AbstractDrillDownChart extends BaseAbstractChart
{
	/**
	 * The rows in the list
	 *
	 * @var bool
	 */
	protected $rows = array();

	/**
	 * The maximum value of data we have
	 *
	 * @var number
	 */
	protected $max_value = 0;

	/**
	 * Adds a row to the list
	 *
	 * @param string $label The label
	 * @param array $data The data points
	 */
	public function addRow($label, $data)
	{
		$sum_data = array_sum($data);

		$this->rows[] = array(
			'label' => $label,
			'data'  => $data,
			'sum_data' => $sum_data
		);

		if ($sum_data > $this->max_value) {
			$this->max_value = $sum_data;
		}
	}

	/**
	 * Get the rows
	 *
	 * @return array The rows
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * Get the max data value
	 *
	 * @return number The max value
	 */
	public function getMaxValue()
	{
		return $this->max_value;
	}

	/**
	 * Calculate the row value as a percentage
	 *
	 * @param number $row_value The row value
	 * @return int The percentage
	 */
	public function calculateRowValuePercentage($row_value)
	{
		if ($this->max_value != 0) {
			return round(($row_value / $this->max_value) * 100);
		}
		else {
			return 0;
		}
	}
	
	/**
	 * Is the chart ready to be rendered, ie do it have all the data it needs
	 *
	 * @var bool
	 */
	public function isChartRenderable()
	{
		$renderable = false;
		
		if (count($this->rows) > 0) {
			$renderable = true;
		}
		
		return $renderable;
	}
}
