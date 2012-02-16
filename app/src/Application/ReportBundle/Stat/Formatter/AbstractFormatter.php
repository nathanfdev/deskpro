<?php

namespace Application\ReportBundle\Stat\Formatter;

abstract class AbstractFormatter implements FormatterInterface
{
	/**
         * Normalize the data set
         * 
         * @param array $data The dataset to normalize
         * @return array
         */
	public function normalizeData($data)
	{		
		return array(
			'unit' => '',
			'data' => $data
		);
	}	
}