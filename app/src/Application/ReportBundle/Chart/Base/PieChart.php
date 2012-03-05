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