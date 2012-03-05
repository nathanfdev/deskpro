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

namespace Application\ReportBundle\Stat\Formatter;

use Orb\Util\Dates;

/**
 * Formats a Time
 */
class TimeFormatter extends AbstractFormatter
{
	protected $normalized_unit = null;

	/**
	 * Get an identifer for the formatter
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'time_formatter';
	}

	/**
         * Normalize the data set. As time can vary across a set of data values
         * we need to get them all in the same unit, such as days, or hours
         *
         * @param array $data The dataset to normalize
         * @return array
         */
	public function normalizeData($data)
	{
		$max = null;
		$min = null;

		foreach($data as $dataSet) {
			$values = $dataSet['values'];

			$dataSetMax = max($values);
			$dataSetMin = min($values);

			// Init min and max on first iteration
			if (true === is_null($min) || true === is_null($max)) {
				$max = $dataSetMax;
				$min = $dataSetMin;
			}

			if (false === is_null($dataSetMin) && $dataSetMin < $min) {
				$min = $dataSetMin;
			}

			if (false === is_null($dataSetMax) && $dataSetMax > $max) {
				$max = $dataSetMax;
			}
		}

		$this->normalized_unit = $this->normalizeValue($max, array(
			'format' => 'unit',
			'normalize_to' => $max
		));

		foreach ($data as &$dataSet) {

			foreach ($dataSet['values'] as &$value) {
				$value = $this->normalizeValue($value, array(
					'format' => 'value',
					'normalize_to' => $max
				));
			}
		}

		return array(
			'unit' => $this->normalized_unit,
			'data' => $data
		);
	}

	/**
	 * Normalize a time value
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * 	- format string: (full|value|unit) The return format - defalt: full
	 * 	- normalize_to: Set this value to normalize the current $data based on this value
	 * @return The formatted data
	 */
	public function normalizeValue($data, array $options = array())
	{
		$format = (isset($options['format'])) ? $options['format'] : 'full';
		$normalize_to = (isset($options['normalize_to'])) ? $options['normalize_to'] : null;

		if ($data > 0) {
			$raw = Dates::secsToPartsArray($data);

			if (true === is_null($normalize_to)) {
				// Get the highest possible time part we can return, starting
				// at year, days, hour, mins, seconds
				foreach ($raw as $part=>$value) {
					if ($value > 0) {
						// We have some unit, return it with the first letter
						// ie, year becomes y, day becomes d
						return $this->getFormattedReturnValue($value, substr($part, 0, 1), $format);
					}
				}
			}
			else {
				$normalizeRaw = Dates::secsToPartsArray($normalize_to);

				foreach ($normalizeRaw as $part=>$value) {
					if ($value > 0) {
						$raw = Dates::secsToPartsArray($data);

						$rawPart = $raw[$part];

						// We have some unit, return it with the first letter
						// ie, year becomes y, day becomes d
						return $this->getFormattedReturnValue($rawPart, substr($part, 0, 1), $format);
					}
				}

			}

		}
		else {
			return null;
		}
	}

	/**
	 * Formats time data
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * 	- format string: (full|value|unit) The return format - defalt: full
	 * 	- normalize_to: Set this value to normalize the current $data based on this value
	 * @return The formatted data
	 */
	public function formatData($data, $unit = '', array $options = array())
	{
		if (is_numeric($data)) {
			return $data . $unit;
		}
		else {
			return $data;
		}
	}

	protected function getFormattedReturnValue($value, $unit, $format)
	{
		switch ($format) {
			case 'full':
				return $value . $unit;
				break;
			case 'value':
				return $value;
				break;
			case 'unit':
				return $unit;
				break;
		}
	}
}
