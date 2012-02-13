<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Formats to an integer
 */
class IntegerFormatter extends AbstractFormatter
{
	/**
	 * Get an identifer for the formatter
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'integer_formatter';
	}
	
	/**
	 * Formats decimal data
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * @return The formatted data
	 */
	public function formatData($data, $unit = '', array $options = array())
	{
		if (is_numeric($data)) {
			return round($data) . $unit;
		}
		else {
			return $data;
		}
	}
}