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

abstract class AbstractChart implements ChartInterface
{
	/**
	 * Unique identifier of chart type
	 */
	const CHART_IDENTIFIER = '';

	protected $chart_type = '';

	protected $chart_vendor = '';

	protected $formatter = null;

	/**
	 * The display units
	 *
	 * @var string
	 */
	protected $display_units = null;

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
	 * Color codes used to render chart lines, bars, etc
	 */
	protected static $color_codes = array(
		'#FF6600', '#FCD202', '#B0DE09', '#0D8ECF', '#2A0CD0', '#CD0D74',
		'#CC0000', '#00CC00', '#0000CC', '#DDDDDD', '#999999', '#333333',
		'#990000'
	);

	/**
	 * Get the view chart vendor
	 *
	 * @return string The view chart vendor
	 */
	public function getViewChartVendor()
	{
		return $this->view_chart_vendor;
	}

	/**
	 * Get the view chart class
	 *
	 * @return string The view chart class
	 */
	public function getViewChartClass()
	{
		return $this->view_chart_class;
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
	 * Text string to display when no data is available
	 *
	 * @return string
	 */
	public function getNoDataLabel()
	{
		return "No data for available for period";
	}

	/**
	 * Calculate the Difference
	 *
	 * @param number $previous_value The previous value
	 * @param number $current_value The current value
	 * @param bool $as_percentage Get the difference as a percentage
	 * @return number The difference
	 */
	public function calculateDifference($previous_value, $current_value, $as_percentage = false)
	{
		$difference = 0;
		if ($previous_value != 0) {
			$difference = ($current_value - $previous_value) / $previous_value;
		}

		return ($as_percentage) ? number_format($difference * 100, 2) : number_format($difference, 2);
	}

	/**
	 * Get the color codes used for the charts
	 *
	 * @return array
	 */
	public function getColorCodes()
	{
		return self::$color_codes;
	}

	/**
	 * Sets a formatter than will process the raw data if required
	 */
	public function setFormatter($formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * Gets the data formatter
	 */
	public function getFormatter()
	{
		return $this->formatter;
	}

	/**
	 * Format the data using the set formatter
	 *
	 * @param mixed $data The data to format
	 * @param array $options Various formatting options
	 * @return mixed The formatted data
	 */
	public function formatData($data, $unit = '', array $options = array())
	{
		if (false === is_null($this->formatter)) {
			$data = $this->formatter->formatData($data, $unit, $options);
		}

		return $data;
	}

	/**
	 * Get the format identifier
	 *
	 * @return string
	 */
	public function getFormatterIdentifier()
	{
		$identifier = '';
		if (false === is_null($this->formatter)) {
			$identifier = $this->formatter->getIdentifier();
		}

		return $identifier;
	}

	/**
	 * Sets the display unit
	 *
	 * @param string $units The display units
	 */
	public function setDisplayUnits($units)
	{
		$this->display_units = $units;
	}

	/**
	 * Get the display units
	 *
	 * @return string The units
	 */
	public function getDisplayUnits()
	{
		return $this->display_units;
	}
}