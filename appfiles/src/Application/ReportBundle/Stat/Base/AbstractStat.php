<?php

namespace Application\ReportBundle\Stat\Base;

abstract class AbstractStat implements StatInterface
{
	protected $available_grouping = array();
	
	protected $trendQueries = array();
	
	protected $results = array();
	
	protected $db;
	
	public function __construct()
	{
		$this->db = App::getDb();
	}
	
	/**
	 * Execute the trend concept query
	 */
	public function executeQueries()
	{
		foreach ($this->trendQueries as $query) {
			$results[] = $this->db->fetchAll($query);
		}
		
		$this->processResults();
	}
	
	public function getAvailableGrouping()
	{
		return $this->available_grouping;
	}
	
	public function removeGrouping($field)
	{
		// Remove field from grouping
	}
	
	public function addGroup($field)
	{
		// Add field to grouping
	}
}