<?php

namespace Application\ReportBundle\Stat\Base;

use Application\ReportBundle\Stat\Searcher\ReportSearchInterface;

interface StatInterface
{
	/**
	 * Initialize the Stat
	 */
	public function init();

	/**
	 * Set the searcher
	 */
	public function setSearcher(ReportSearchInterface $searcher);

	/**
	 * Build the Trend Concept Query. This is the base query that needs
	 * to be defined in each of the parent Trend Type classes
	 */
	public function buildConceptQueries();

	public function processUngroupedResults($result);

	/**
	 * Process the returned result from the queries. May need to format results,
	 * peform additional processing, etc
	 */
	public function processGroupedResults($result);

	/**
	 * Get the data formatter
	 */
	public static function getFormatter();
}