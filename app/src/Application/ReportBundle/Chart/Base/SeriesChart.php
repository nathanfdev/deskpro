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

namespace Application\ReportBundle\Chart\Base;

/**
 * Base Series chart, useful for line and column type charts
 */
abstract class SeriesChart extends AbstractChart
{
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
	 * @var string
	 */
	protected $y_label = '';

	/**
	 * @var string
	 */
	protected $x_label = '';

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

		$this->setMinMaxValues(max($data));
		$this->setMinMaxValues(min($data));
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
	 * @param string $y_label
	 */
	public function setYLabel($y_label)
	{
		$this->y_label = $y_label;
	}

	/**
	 * @return string
	 */
	public function getYLabel()
	{
		return $this->y_label;
	}

	/**
	 * @param string $x_label
	 */
	public function setXLabel($x_label)
	{
		$this->x_label = $x_label;
	}

	/**
	 * @return string
	 */
	public function getXLabel()
	{
		return $this->x_label;
	}
}