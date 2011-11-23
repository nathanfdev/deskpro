<?php

namespace Application\ReportBundle\Chart\Base;

abstract class PieChart extends AbstractChart
{
	const CHART_IDENTIFIER = 'pie';

	/**
	 * The pie chart slices
	 */
	protected $slices = array();

	/**
	 * The graph labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * The max value in the pie chart
	 *
	 * @var number
	 */
	protected $max = null;

	/**
	 * The min value in the pie chart
	 *
	 * @var number
	 */
	protected $min = null;

	/**
	 * Adds a slice
	 *
	 * @param string $label The label
	 * @param float $value The data points
	 */
	public function addSlice($label, $value)
	{
		$this->slices[] = array(
			'label' => $label,
			'value' => $value
		);

		$this->addLabel($label);

		$this->setMinMaxValues($value);
	}

	/**
	 * Adds a label to the Chart
	 *
	 * @param string $label The label
	 */
	public function addLabel($label)
	{
		$this->labels[] = $label;
	}

	/**
	 * Get a list of labels
	 *
	 * @return array List of labels
	 */
	public function getLabels()
	{
		return $this->labels;
	}

	/**
	 * Get the pie chart slices
	 *
	 * @return array The slices
	 */
	public function getSlices()
	{
		return $this->slices;
	}

	/**
	 * Is the chart ready to be rendered, ie do it have all the data it needs
	 *
	 * @var bool
	 */
	public function isChartRenderable()
	{
		$renderable = true;

		return $renderable;
	}

	/**
	 * Set the current min and max values
	 *
	 * @param number $value
	 */
	protected function setMinMaxValues($value)
	{
		if (true === is_null($this->min)) {
			$this->min = $value;
		}
		else if ($value < $this->min) {
			$this->min = $value;
		}

		if (true === is_null($this->max)) {
			$this->max = $value;
		}
		else if ($value > $this->max) {
			$this->max = $value;
		}
	}

	/**
	 * Get the max value from the data set
	 *
	 * @return number The max value
	 */
	public function getMax()
	{
		return $this->max;
	}

	/**
	 * Get the min value from the data set
	 *
	 * @return number The min value
	 */
	public function getMin()
	{
		return $this->min;
	}

	/**
	 * Sort the data
	 *
	 * @param string $sort_by The data field to sort on (label|value)
	 * @param string $direction The direction (asc|desc)
	 */
	public function sortData($sort_by = 'label', $direction = 'asc')
	{
		usort($this->slices, function($a,$b) use($sort_by, $direction) {
			if ('asc' === $direction) {
				return $a[$sort_by]>$b[$sort_by];
			}
			else {
				return $a[$sort_by]<$b[$sort_by];
			}
		});
	}

}