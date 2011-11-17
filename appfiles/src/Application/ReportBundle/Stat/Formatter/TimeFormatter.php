<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Formats a Time
 */
class TimeFormatter implements FormatterInterface
{
	/**
	 * Formats time data
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * @return The formatted data
	 */
	public function formatData($data, array $options = array())
	{
		$raw = \Orb\Util\Dates::secsToPartsArray($data);
		
		return $raw['hours'] . 'h';
	}
}