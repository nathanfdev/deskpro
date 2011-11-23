<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Formats to an integer
 */
class IntegerFormatter implements FormatterInterface
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
	public function formatData($data, array $options = array())
	{
		return round($data);
	}
}