<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Simple data formatter interface
 */
interface FormatterInterface
{
	/**
	 * Formats some data
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * @return The formatted data
	 */
	public function formatData($data, array $options = array());
}