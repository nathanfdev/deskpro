<?php

namespace Application\ReportBundle\Stat\Base;

interface StatInterface
{
	/**
	 * Build the Trend Concept Query. This is the base query that needs
	 * to be defined in each of the parent Trend Type classes
	 */
	public function buildConceptQueries();
	
	/**
	 * Process the returned result from the queries. May need to format results,
	 * peform additional processing, etc
	 */
	public function processResults();
}