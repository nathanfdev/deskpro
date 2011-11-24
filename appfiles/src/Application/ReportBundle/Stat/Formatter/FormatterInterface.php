<?php

namespace Application\ReportBundle\Stat\Formatter;

/**
 * Simple data formatter interface
 */
interface FormatterInterface
{
	/**
	 * Get an identifer for the formatter
	 *
	 * @return string
	 */
	public function getIdentifier();

	/**
	 * Formats some data
	 *
	 * @param mixed $data
	 * @param array $options Various formatting options
	 * @return The formatted data
	 */
	public function formatData($data, $unit = '', array $options = array());

	/**
         * Normalize the data set
         *
         * @param array $data The dataset to normalize
         * @return array
         */
	public function normalizeData($data);
}