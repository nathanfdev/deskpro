<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Formats to an integer
 */
class IntegerFormatter implements FormatterInterface
{
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