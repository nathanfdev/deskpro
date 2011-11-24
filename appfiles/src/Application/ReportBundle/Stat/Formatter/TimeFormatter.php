<?php

namespace Application\ReportBundle\Stat\Formatter;

use Orb\Util\Dates;

/**
 * Formats a Time
 */
class TimeFormatter implements FormatterInterface
{
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

		foreach($data as &$dataSet) {

			foreach ( $dataSet['values'] as &$value) {
				$value = $this->formatData($value, array(
					'format' => 'value',
					'normalize_to' => $max
				));
			}
		}

		return $data;
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
	public function formatData($data, array $options = array())
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
			return '';
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