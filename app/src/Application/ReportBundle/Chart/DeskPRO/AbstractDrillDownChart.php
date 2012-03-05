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
	 * Label used to describe the rows in the chart
	 *
	 * @var string
	 */
	protected $data_label = 'Name';

	/**
	 * Adds a row to the list
	 *
	 * @param string $label The label
	 * @param array $data The data points
	 */
	public function addRow($label, $data, $row_sum)
	{
		$this->rows[] = array(
			'label' => $label,
			'data'  => $data,
			'sum_data' => $row_sum
		);

		if ($row_sum > $this->max_value) {
			$this->max_value = $row_sum;
		}

		$this->setMinMaxValues(max($data));
		$this->setMinMaxValues(min($data));
	}

	/**
	 * Sort the data
	 *
	 * @param string $sort_by The data field to sort on (label|sum_data)
	 * @param string $direction The direction (asc|desc)
	 */
	public function sortData($sort_by = 'label', $direction = 'asc')
	{
		usort($this->rows, function($a,$b) use($sort_by, $direction) {
			if ('asc' === $direction) {
				return $a[$sort_by]>$b[$sort_by];
			}
			else {
				return $a[$sort_by]<$b[$sort_by];
			}
		});
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
	 * Get the data label
	 *
	 * @return string The data label
	 */
	public function getDataLabel()
	{
		return $this->data_label;
	}

	/**
	 * Set the data label
	 *
	 * @param string $data_label The data label to set
	 */
	public function setDataLabel($data_label)
	{
		$this->data_label = $data_label;
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
