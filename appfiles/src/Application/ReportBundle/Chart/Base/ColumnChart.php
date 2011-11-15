<?php

namespace Application\ReportBundle\Chart\Base;

abstract class ColumnChart extends AbstractChart
{
	const CHART_IDENTIFIER = 'column';

	/**
	 * The line graph series (x-axis values)
	 *
	 * @var array
	 */
	protected $series = array();

	/**
	 * The graphs (points of data)
	 *
	 * @var array
	 */
	protected $graphs = array();

	/**
	 * The graph labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Adds a graph
	 *
	 * @param string $label The label
	 * @param array $data The data points
	 */
	public function addGraph($label, $data)
	{
		$this->graphs[] = array(
			'label' => $label,
			'data'  => $data
		);

		$this->addLabel($label);
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
	 * Adds a series to the chart
	 *
	 * @param string $series The series label
	 */
	public function addSeries($series)
	{
		$this->series[] = $series;
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
	 * Get the graphs
	 *
	 * @return array The graphs
	 */
	public function getGraphs()
	{
		return $this->graphs;
	}

	/**
	 * Get the series
	 *
	 * @return array The series
	 */
	public function getSeries()
	{
		return $this->series;
	}
}